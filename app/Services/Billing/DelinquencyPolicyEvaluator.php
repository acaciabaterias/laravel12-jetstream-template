<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\EventoComercialAssinante;
use App\Models\FaturaSaaS;
use App\Models\PoliticaInadimplencia;
use App\Support\Billing\CommercialEventType;
use App\Support\Billing\DelinquencyAction;
use App\Support\Billing\PlatformSubscriptionStatus;
use App\Support\Billing\SaasInvoiceStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DelinquencyPolicyEvaluator
{
    public function __construct(
        private readonly PlatformSubscriptionStateMachine $stateMachine = new PlatformSubscriptionStateMachine,
        private readonly SaasInvoiceService $saasInvoiceService = new SaasInvoiceService,
    ) {}

    public function decideAction(
        ?FaturaSaaS $faturaSaaS,
        ?PoliticaInadimplencia $politicaInadimplencia = null,
        ?string $currentStatus = null,
        ?Carbon $referenceDate = null,
    ): DelinquencyAction {
        $referenceDate ??= now();
        $policy = $this->resolvePolicy($politicaInadimplencia);

        if ($faturaSaaS === null) {
            return in_array($currentStatus, [
                PlatformSubscriptionStatus::Blocked->value,
                PlatformSubscriptionStatus::GracePeriod->value,
            ], true)
                ? DelinquencyAction::ReactivateSubscriber
                : DelinquencyAction::None;
        }

        if ($faturaSaaS->status === SaasInvoiceStatus::Paid->value) {
            return in_array($currentStatus, [
                PlatformSubscriptionStatus::Blocked->value,
                PlatformSubscriptionStatus::GracePeriod->value,
            ], true)
                ? DelinquencyAction::ReactivateSubscriber
                : DelinquencyAction::None;
        }

        if ($referenceDate->startOfDay()->lte($faturaSaaS->vencimento->startOfDay())) {
            return DelinquencyAction::None;
        }

        $daysOverdue = $faturaSaaS->vencimento->startOfDay()->diffInDays($referenceDate->startOfDay());

        if ($faturaSaaS->status === SaasInvoiceStatus::Pending->value) {
            return DelinquencyAction::MarkOverdue;
        }

        if ($policy['block_after_days'] > 0 && $daysOverdue >= $policy['block_after_days']) {
            return DelinquencyAction::BlockSubscriber;
        }

        if ($policy['grace_period_days'] > 0 && $daysOverdue >= $policy['grace_period_days']) {
            return DelinquencyAction::StartGracePeriod;
        }

        return DelinquencyAction::None;
    }

    public function assess(AssinaturaPlataforma $assinaturaPlataforma, ?Carbon $referenceDate = null): AssinaturaPlataforma
    {
        $referenceDate ??= now();

        return DB::connection('central')->transaction(function () use ($assinaturaPlataforma, $referenceDate): AssinaturaPlataforma {
            $assinatura = AssinaturaPlataforma::query()
                ->with(['cliente', 'politicaInadimplencia', 'faturas'])
                ->findOrFail($assinaturaPlataforma->id);

            $openInvoice = $assinatura->faturas()
                ->whereIn('status', [
                    SaasInvoiceStatus::Pending->value,
                    SaasInvoiceStatus::Overdue->value,
                ])
                ->orderBy('vencimento')
                ->first();

            $action = $this->decideAction(
                faturaSaaS: $openInvoice,
                politicaInadimplencia: $assinatura->politicaInadimplencia,
                currentStatus: $assinatura->status,
                referenceDate: $referenceDate,
            );

            if ($action === DelinquencyAction::MarkOverdue && $openInvoice !== null) {
                $openInvoice = $this->saasInvoiceService->markAsOverdue($openInvoice, $referenceDate);
                $action = $this->decideAction(
                    faturaSaaS: $openInvoice,
                    politicaInadimplencia: $assinatura->politicaInadimplencia,
                    currentStatus: $assinatura->status,
                    referenceDate: $referenceDate,
                );
            }

            return match ($action) {
                DelinquencyAction::StartGracePeriod => $this->startGracePeriod($assinatura, $openInvoice, $referenceDate),
                DelinquencyAction::BlockSubscriber => $this->blockSubscriber($assinatura, $openInvoice, $referenceDate),
                DelinquencyAction::ReactivateSubscriber => $this->reactivateSubscriber($assinatura, $referenceDate),
                default => $assinatura->refresh(),
            };
        });
    }

    /**
     * @return array{grace_period_days:int, block_after_days:int, reactivation_mode:string, notification_profile:array<string, mixed>}
     */
    private function resolvePolicy(?PoliticaInadimplencia $politicaInadimplencia): array
    {
        return [
            'grace_period_days' => (int) ($politicaInadimplencia?->grace_period_days ?? config('platform_billing.delinquency.grace_period_days', 3)),
            'block_after_days' => (int) ($politicaInadimplencia?->block_after_days ?? config('platform_billing.delinquency.block_after_days', 7)),
            'reactivation_mode' => (string) ($politicaInadimplencia?->reactivation_mode ?? config('platform_billing.delinquency.reactivation_mode', 'automatic')),
            'notification_profile' => $politicaInadimplencia?->notification_profile ?? config('platform_billing.delinquency.notification_profile', []),
        ];
    }

    private function startGracePeriod(
        AssinaturaPlataforma $assinaturaPlataforma,
        ?FaturaSaaS $faturaSaaS,
        Carbon $referenceDate,
    ): AssinaturaPlataforma {
        if ($assinaturaPlataforma->status === PlatformSubscriptionStatus::GracePeriod->value) {
            return $assinaturaPlataforma->refresh();
        }

        if (! $this->stateMachine->canTransition($assinaturaPlataforma->status, PlatformSubscriptionStatus::GracePeriod->value)) {
            throw new InvalidArgumentException('Transicao invalida para grace period.');
        }

        $beforeState = $this->snapshot($assinaturaPlataforma);
        $policy = $this->resolvePolicy($assinaturaPlataforma->politicaInadimplencia);
        $graceEndsAt = $faturaSaaS?->vencimento?->copy()->addDays($policy['block_after_days'] ?: $policy['grace_period_days']);

        $assinaturaPlataforma->update([
            'status' => PlatformSubscriptionStatus::GracePeriod->value,
            'grace_ends_at' => $graceEndsAt?->toDateString(),
        ]);

        $this->logEvent(
            cliente: $assinaturaPlataforma->cliente,
            assinatura: $assinaturaPlataforma->refresh(),
            eventType: CommercialEventType::GraceStarted,
            beforeState: $beforeState,
            afterState: $this->snapshot($assinaturaPlataforma->refresh()),
            reason: 'Politica de grace period aplicada por fatura em atraso.',
            effectiveAt: $referenceDate,
            metadata: [
                'invoice_id' => $faturaSaaS?->id,
                'invoice_reference' => $faturaSaaS?->referencia,
            ],
        );

        $this->publishBackboneEvent(
            eventType: 'GRACE_PERIOD_INICIADO',
            assinaturaPlataforma: $assinaturaPlataforma->refresh()->loadMissing('cliente', 'plano'),
            payload: [
                'subscription_id' => $assinaturaPlataforma->id,
                'tenant_id' => $assinaturaPlataforma->cliente_id,
                'invoice_id' => $faturaSaaS?->id,
                'status' => $assinaturaPlataforma->status,
                'grace_ends_at' => $graceEndsAt?->toDateString(),
            ],
            consumers: ['platform', 'ms-003'],
            schemaDefinition: ['subscription_id' => 'integer', 'tenant_id' => 'integer', 'invoice_id' => 'integer|null', 'status' => 'string', 'grace_ends_at' => 'date|null'],
        );

        return $assinaturaPlataforma->refresh();
    }

    private function blockSubscriber(
        AssinaturaPlataforma $assinaturaPlataforma,
        ?FaturaSaaS $faturaSaaS,
        Carbon $referenceDate,
    ): AssinaturaPlataforma {
        if ($assinaturaPlataforma->status === PlatformSubscriptionStatus::Blocked->value) {
            return $assinaturaPlataforma->refresh();
        }

        if (! $this->stateMachine->canTransition($assinaturaPlataforma->status, PlatformSubscriptionStatus::Blocked->value)) {
            throw new InvalidArgumentException('Transicao invalida para bloqueio.');
        }

        $beforeState = $this->snapshot($assinaturaPlataforma);

        $assinaturaPlataforma->update([
            'status' => PlatformSubscriptionStatus::Blocked->value,
            'blocked_at' => $referenceDate,
            'blocked_reason' => 'Inadimplencia SaaS acima do limite configurado.',
        ]);

        $assinaturaPlataforma->cliente->update([
            'status' => PlatformSubscriptionStatus::Suspended->value,
            'billing_blocked' => true,
        ]);

        $this->logEvent(
            cliente: $assinaturaPlataforma->cliente->refresh(),
            assinatura: $assinaturaPlataforma->refresh(),
            eventType: CommercialEventType::SubscriberBlocked,
            beforeState: $beforeState,
            afterState: $this->snapshot($assinaturaPlataforma->refresh()),
            reason: 'Assinante bloqueado por inadimplencia SaaS.',
            effectiveAt: $referenceDate,
            metadata: [
                'invoice_id' => $faturaSaaS?->id,
                'invoice_reference' => $faturaSaaS?->referencia,
            ],
        );

        $this->publishBackboneEvent(
            eventType: 'ASSINANTE_BLOQUEADO',
            assinaturaPlataforma: $assinaturaPlataforma->refresh()->loadMissing('cliente', 'plano'),
            payload: [
                'subscription_id' => $assinaturaPlataforma->id,
                'tenant_id' => $assinaturaPlataforma->cliente_id,
                'invoice_id' => $faturaSaaS?->id,
                'status' => $assinaturaPlataforma->status,
                'blocked_at' => $referenceDate->toIso8601String(),
            ],
            consumers: ['platform', 'ms-003'],
            schemaDefinition: ['subscription_id' => 'integer', 'tenant_id' => 'integer', 'invoice_id' => 'integer|null', 'status' => 'string', 'blocked_at' => 'datetime'],
        );

        return $assinaturaPlataforma->refresh();
    }

    private function reactivateSubscriber(AssinaturaPlataforma $assinaturaPlataforma, Carbon $referenceDate): AssinaturaPlataforma
    {
        if (! in_array($assinaturaPlataforma->status, [
            PlatformSubscriptionStatus::Blocked->value,
            PlatformSubscriptionStatus::GracePeriod->value,
        ], true)) {
            return $assinaturaPlataforma->refresh();
        }

        if (! $this->stateMachine->canTransition($assinaturaPlataforma->status, PlatformSubscriptionStatus::Active->value)) {
            throw new InvalidArgumentException('Transicao invalida para reativacao.');
        }

        $beforeState = $this->snapshot($assinaturaPlataforma);

        $assinaturaPlataforma->update([
            'status' => PlatformSubscriptionStatus::Active->value,
            'grace_ends_at' => null,
            'blocked_at' => null,
            'blocked_reason' => null,
            'reactivated_at' => $referenceDate,
        ]);

        $assinaturaPlataforma->cliente->update([
            'status' => PlatformSubscriptionStatus::Active->value,
            'billing_blocked' => false,
        ]);

        $this->logEvent(
            cliente: $assinaturaPlataforma->cliente->refresh(),
            assinatura: $assinaturaPlataforma->refresh(),
            eventType: CommercialEventType::SubscriberReactivated,
            beforeState: $beforeState,
            afterState: $this->snapshot($assinaturaPlataforma->refresh()),
            reason: 'Assinante reativado apos regularizacao financeira.',
            effectiveAt: $referenceDate,
        );

        $this->publishBackboneEvent(
            eventType: 'ASSINANTE_REATIVADO',
            assinaturaPlataforma: $assinaturaPlataforma->refresh()->loadMissing('cliente', 'plano'),
            payload: [
                'subscription_id' => $assinaturaPlataforma->id,
                'tenant_id' => $assinaturaPlataforma->cliente_id,
                'status' => $assinaturaPlataforma->status,
                'reactivated_at' => $referenceDate->toIso8601String(),
            ],
            consumers: ['platform', 'ms-003'],
            schemaDefinition: ['subscription_id' => 'integer', 'tenant_id' => 'integer', 'status' => 'string', 'reactivated_at' => 'datetime'],
        );

        return $assinaturaPlataforma->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(AssinaturaPlataforma $assinaturaPlataforma): array
    {
        return [
            'status' => $assinaturaPlataforma->status,
            'plano_id' => $assinaturaPlataforma->plano_id,
            'grace_ends_at' => $assinaturaPlataforma->grace_ends_at?->toDateString(),
            'blocked_at' => $assinaturaPlataforma->blocked_at?->toIso8601String(),
            'reactivated_at' => $assinaturaPlataforma->reactivated_at?->toIso8601String(),
            'cancel_reason' => $assinaturaPlataforma->cancel_reason,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $beforeState
     * @param  array<string, mixed>|null  $afterState
     * @param  array<string, mixed>  $metadata
     */
    private function logEvent(
        Cliente $cliente,
        AssinaturaPlataforma $assinatura,
        CommercialEventType $eventType,
        ?array $beforeState,
        ?array $afterState,
        string $reason,
        Carbon $effectiveAt,
        array $metadata = [],
    ): EventoComercialAssinante {
        return EventoComercialAssinante::query()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'event_type' => $eventType->value,
            'before_state' => $beforeState,
            'after_state' => $afterState,
            'effective_at' => $effectiveAt,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $consumers
     * @param  array<string, mixed>  $schemaDefinition
     */
    private function publishBackboneEvent(
        string $eventType,
        AssinaturaPlataforma $assinaturaPlataforma,
        array $payload,
        array $consumers,
        array $schemaDefinition,
    ): void {
        app(PlatformBillingEventPublisher::class)->publish(
            eventType: $eventType,
            assinaturaPlataforma: $assinaturaPlataforma,
            payload: $payload,
            consumers: $consumers,
            schemaDefinition: $schemaDefinition,
        );
    }
}
