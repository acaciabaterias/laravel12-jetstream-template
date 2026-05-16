<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CasoRecuperacaoReceita;
use App\Models\CompromissoPagamento;
use App\Models\UsuarioPlataforma;
use App\Support\Billing\PaymentPromiseStatus;
use App\Support\Billing\RevenueRecoveryActionType;
use App\Support\Billing\RevenueRecoveryCaseStatus;

class PaymentPromiseService
{
    public function __construct(
        private readonly PlatformRevenueRecoveryEventPublisher $eventPublisher,
    ) {}

    /**
     * @param  array{promised_amount?: float|int|string|null, promised_date: string, notes?: string|null, suspends_until?: string|null}  $attributes
     */
    public function record(CasoRecuperacaoReceita $casoRecuperacaoReceita, UsuarioPlataforma $actor, array $attributes): CompromissoPagamento
    {
        $case = $casoRecuperacaoReceita->loadMissing('fatura.cliente');

        $promise = $case->compromissos()->create([
            'promised_amount' => $attributes['promised_amount'] ?? null,
            'promised_date' => $attributes['promised_date'],
            'status' => PaymentPromiseStatus::Open->value,
            'recorded_by_user_id' => $actor->id,
            'notes' => $attributes['notes'] ?? null,
            'suspends_until' => $attributes['suspends_until'] ?? $attributes['promised_date'],
            'metadata' => ['source' => 'manual_promise'],
        ]);

        $case->acoes()
            ->where('status', 'scheduled')
            ->whereIn('action_type', [
                RevenueRecoveryActionType::AutomatedReminder->value,
                RevenueRecoveryActionType::PromiseFollowUp->value,
            ])
            ->get()
            ->filter(fn ($action) => $this->shouldSuspendAction($action->channel))
            ->each(function ($action) use ($promise): void {
                $action->update([
                    'status' => 'skipped',
                    'result_code' => 'suspended_by_promise',
                    'metadata' => array_merge((array) $action->metadata, [
                        'promise_id' => $promise->id,
                        'suspended_at' => now()->toIso8601String(),
                    ]),
                ]);
            });

        $case->update([
            'status' => RevenueRecoveryCaseStatus::Paused->value,
            'last_action_at' => now(),
            'metadata' => array_merge((array) $case->metadata, [
                'active_promise_id' => $promise->id,
            ]),
        ]);

        $case->acoes()->create([
            'action_type' => RevenueRecoveryActionType::PromiseFollowUp->value,
            'channel' => 'internal_task',
            'stage_name' => $case->current_stage,
            'status' => 'completed',
            'idempotency_key' => sha1(sprintf('recovery-promise:%d:%d', $case->id, $promise->id)),
            'executed_at' => now(),
            'result_code' => 'promise_recorded',
            'operator_user_id' => $actor->id,
            'payload_snapshot' => [
                'promise_id' => $promise->id,
                'promised_date' => $promise->promised_date?->toDateString(),
            ],
            'metadata' => ['source' => 'payment_promise_service'],
        ]);

        if ($case->fatura !== null) {
            $this->eventPublisher->publish(
                eventType: 'PROMESSA_PAGAMENTO_REGISTRADA',
                faturaSaaS: $case->fatura,
                payload: [
                    'case_id' => $case->id,
                    'promise_id' => $promise->id,
                    'invoice_id' => $case->fatura_saas_id,
                    'promised_date' => $promise->promised_date?->toDateString(),
                    'operator_user_id' => $actor->id,
                ],
                consumers: ['platform', 'analytics'],
                schemaDefinition: [
                    'case_id' => 'integer',
                    'promise_id' => 'integer',
                    'invoice_id' => 'integer',
                    'promised_date' => 'string',
                    'operator_user_id' => 'integer',
                ],
            );
        }

        return $promise->refresh();
    }

    public function shouldSuspendAction(string $channel): bool
    {
        return in_array($channel, ['email', 'whatsapp', 'phone'], true);
    }
}
