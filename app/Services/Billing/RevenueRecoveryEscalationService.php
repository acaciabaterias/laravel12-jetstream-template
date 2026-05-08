<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CasoRecuperacaoReceita;
use App\Models\UsuarioPlataforma;
use App\Support\Billing\RevenueRecoveryActionType;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use App\Support\Billing\RevenueRecoverySeverity;

class RevenueRecoveryEscalationService
{
    public function __construct(
        private readonly PlatformRevenueRecoveryEventPublisher $eventPublisher,
    ) {}

    public function calculateScore(CasoRecuperacaoReceita $casoRecuperacaoReceita): int
    {
        $overdueDays = $this->overdueDays($casoRecuperacaoReceita);
        $failedActions = $casoRecuperacaoReceita->relationLoaded('acoes')
            ? $casoRecuperacaoReceita->acoes
                ->where('status', 'failed')
                ->count()
            : $casoRecuperacaoReceita->acoes()
                ->where('status', 'failed')
                ->count();

        return $overdueDays + ($failedActions * 2) + match ($casoRecuperacaoReceita->severity) {
            RevenueRecoverySeverity::Critical => 6,
            RevenueRecoverySeverity::High => 4,
            RevenueRecoverySeverity::Medium => 2,
            RevenueRecoverySeverity::Low => 1,
        };
    }

    public function shouldEscalate(CasoRecuperacaoReceita $casoRecuperacaoReceita): bool
    {
        if ($casoRecuperacaoReceita->status === RevenueRecoveryCaseStatus::Escalated) {
            return false;
        }

        $minimumScore = (int) config('platform_revenue_recovery.escalation.days_overdue', 7);

        return $this->calculateScore($casoRecuperacaoReceita) >= $minimumScore;
    }

    public function escalate(
        CasoRecuperacaoReceita $casoRecuperacaoReceita,
        ?UsuarioPlataforma $owner = null,
        ?UsuarioPlataforma $actor = null,
        string $reason = 'Escalonamento automatico do caso de recuperacao.',
    ): CasoRecuperacaoReceita {
        $case = $casoRecuperacaoReceita->loadMissing('fatura.cliente');

        if (! $this->shouldEscalate($case)) {
            return $case;
        }

        $resolvedOwner = $owner ?? $actor ?? UsuarioPlataforma::query()
            ->where('papel', 'billing')
            ->where('ativo', true)
            ->orderBy('id')
            ->first();

        $case->update([
            'status' => RevenueRecoveryCaseStatus::Escalated->value,
            'owner_user_id' => $resolvedOwner?->id,
            'severity' => RevenueRecoverySeverity::Critical->value,
            'last_action_at' => now(),
            'metadata' => array_merge((array) $case->metadata, [
                'escalation_reason' => $reason,
                'escalated_at' => now()->toIso8601String(),
            ]),
        ]);

        $action = $case->acoes()->create([
            'action_type' => RevenueRecoveryActionType::Escalation->value,
            'channel' => 'internal_task',
            'stage_name' => $case->current_stage,
            'status' => 'completed',
            'idempotency_key' => sha1(sprintf('recovery-escalation:%d:%s', $case->id, $case->current_stage)),
            'executed_at' => now(),
            'result_code' => 'escalated',
            'operator_user_id' => $resolvedOwner?->id,
            'payload_snapshot' => ['reason' => $reason],
            'metadata' => ['source' => 'revenue_recovery_escalation'],
        ]);

        if ($case->fatura !== null) {
            $this->eventPublisher->publish(
                eventType: 'CASO_RECUPERACAO_ESCALADO',
                faturaSaaS: $case->fatura,
                payload: [
                    'case_id' => $case->id,
                    'action_id' => $action->id,
                    'invoice_id' => $case->fatura_saas_id,
                    'owner_user_id' => $resolvedOwner?->id,
                    'reason' => $reason,
                ],
                consumers: ['platform'],
                schemaDefinition: [
                    'case_id' => 'integer',
                    'action_id' => 'integer',
                    'invoice_id' => 'integer',
                    'owner_user_id' => 'integer|null',
                    'reason' => 'string',
                ],
            );
        }

        return $case->refresh();
    }

    private function overdueDays(CasoRecuperacaoReceita $casoRecuperacaoReceita): int
    {
        if ($casoRecuperacaoReceita->fatura?->vencimento === null) {
            return 0;
        }

        return (int) max(0, now()->startOfDay()->diffInDays($casoRecuperacaoReceita->fatura->vencimento, false) * -1);
    }
}
