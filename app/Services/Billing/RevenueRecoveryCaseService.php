<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CasoRecuperacaoReceita;
use App\Models\CobrancaSaaSExterna;
use App\Models\FaturaSaaS;
use App\Support\Billing\RevenueRecoverySeverity;
use Illuminate\Database\ConnectionInterface;

class RevenueRecoveryCaseService
{
    public function __construct(
        private readonly RevenueRecoveryPolicyService $policyService,
        private readonly RevenueRecoveryActionScheduler $actionScheduler,
        private readonly PlatformRevenueRecoveryEventPublisher $eventPublisher,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function openForInvoice(
        FaturaSaaS $faturaSaaS,
        string $entryReason = 'invoice_overdue',
        array $context = []
    ): CasoRecuperacaoReceita {
        /** @var ConnectionInterface $connection */
        $connection = CasoRecuperacaoReceita::query()->getModel()->getConnection();

        return $connection->transaction(function () use ($faturaSaaS, $entryReason, $context): CasoRecuperacaoReceita {
            $invoice = $faturaSaaS->loadMissing(['cliente', 'assinatura']);
            $policy = $this->policyService->resolveForInvoice($invoice);
            $stageDefinition = $this->policyService->resolveInitialStage($policy);

            $existingCase = CasoRecuperacaoReceita::query()
                ->where('fatura_saas_id', $invoice->id)
                ->where('politica_recuperacao_receita_id', $policy->id)
                ->whereIn('status', ['open', 'paused', 'escalated'])
                ->first();

            if ($existingCase !== null) {
                $this->actionScheduler->schedule($existingCase->loadMissing('fatura.cliente'), $stageDefinition, $context);

                return $existingCase->refresh();
            }

            $case = CasoRecuperacaoReceita::query()->create([
                'cliente_id' => $invoice->cliente_id,
                'assinatura_id' => $invoice->assinatura_id,
                'fatura_saas_id' => $invoice->id,
                'politica_recuperacao_receita_id' => $policy->id,
                'status' => 'open',
                'entry_reason' => $entryReason,
                'current_stage' => $stageDefinition['name'],
                'severity' => $this->determineSeverity($invoice, $entryReason)->value,
                'opened_at' => now(),
                'last_action_at' => null,
                'metadata' => [
                    'context' => $context,
                ],
            ]);

            $this->eventPublisher->publish(
                eventType: 'RECUPERACAO_RECEITA_INICIADA',
                faturaSaaS: $invoice,
                payload: [
                    'case_id' => $case->id,
                    'invoice_id' => $invoice->id,
                    'entry_reason' => $entryReason,
                    'stage_name' => $stageDefinition['name'],
                    'severity' => $case->severity->value,
                ],
                consumers: config('platform_revenue_recovery.events.default_consumers', ['platform', 'analytics', 'ms-003']),
                schemaDefinition: [
                    'case_id' => 'integer',
                    'invoice_id' => 'integer',
                    'entry_reason' => 'string',
                    'stage_name' => 'string',
                    'severity' => 'string',
                ],
            );

            $this->actionScheduler->schedule($case->loadMissing('fatura.cliente'), $stageDefinition, $context);

            return $case->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function openFromPaymentFailure(CobrancaSaaSExterna $cobrancaSaaSExterna, array $context = []): CasoRecuperacaoReceita
    {
        $charge = $cobrancaSaaSExterna->loadMissing('fatura.cliente', 'fatura.assinatura');

        return $this->openForInvoice(
            faturaSaaS: $charge->fatura,
            entryReason: 'payment_failed',
            context: array_merge([
                'charge_id' => $charge->id,
                'failure_reason' => $charge->failure_reason,
            ], $context),
        );
    }

    private function determineSeverity(FaturaSaaS $faturaSaaS, string $entryReason): RevenueRecoverySeverity
    {
        if ($entryReason === 'payment_failed') {
            return RevenueRecoverySeverity::High;
        }

        $daysOverdue = now()->startOfDay()->diffInDays($faturaSaaS->vencimento, false) * -1;
        $threshold = (int) config('platform_revenue_recovery.escalation.days_overdue', 7);

        if ($daysOverdue >= $threshold) {
            return RevenueRecoverySeverity::High;
        }

        if ($daysOverdue >= max(1, $threshold - 3)) {
            return RevenueRecoverySeverity::Medium;
        }

        return RevenueRecoverySeverity::Low;
    }
}
