<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\AcaoRecuperacaoReceita;
use App\Models\CasoRecuperacaoReceita;

class RevenueRecoveryActionScheduler
{
    public function __construct(
        private readonly PlatformRevenueRecoveryEventPublisher $eventPublisher,
    ) {}

    /**
     * @param  array{name: string, channel: string, delay_hours: int}  $stageDefinition
     * @param  array<string, mixed>  $context
     */
    public function schedule(CasoRecuperacaoReceita $casoRecuperacaoReceita, array $stageDefinition, array $context = []): AcaoRecuperacaoReceita
    {
        $idempotencyKey = $this->makeIdempotencyKey(
            casoRecuperacaoReceita: $casoRecuperacaoReceita,
            stageName: $stageDefinition['name'],
            channel: $stageDefinition['channel'],
        );

        $existingAction = $casoRecuperacaoReceita->acoes()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingAction !== null) {
            return $existingAction;
        }

        $action = $casoRecuperacaoReceita->acoes()->create([
            'action_type' => 'automated_reminder',
            'channel' => $stageDefinition['channel'],
            'stage_name' => $stageDefinition['name'],
            'status' => 'scheduled',
            'idempotency_key' => $idempotencyKey,
            'scheduled_for' => now()->addHours($stageDefinition['delay_hours']),
            'payload_snapshot' => [
                'context' => $context,
                'stage_definition' => $stageDefinition,
            ],
            'metadata' => [
                'source' => 'revenue_recovery_scheduler',
            ],
        ]);

        $casoRecuperacaoReceita->update([
            'last_action_at' => now(),
        ]);

        if ($casoRecuperacaoReceita->fatura !== null) {
            $this->eventPublisher->publish(
                eventType: 'ACAO_COBRANCA_AGENDADA',
                faturaSaaS: $casoRecuperacaoReceita->fatura->loadMissing('cliente'),
                payload: [
                    'case_id' => $casoRecuperacaoReceita->id,
                    'action_id' => $action->id,
                    'invoice_id' => $casoRecuperacaoReceita->fatura_saas_id,
                    'stage_name' => $stageDefinition['name'],
                    'channel' => $stageDefinition['channel'],
                ],
                consumers: config('platform_revenue_recovery.events.default_consumers', ['platform', 'ms-003']),
                schemaDefinition: [
                    'case_id' => 'integer',
                    'action_id' => 'integer',
                    'invoice_id' => 'integer',
                    'stage_name' => 'string',
                    'channel' => 'string',
                ],
            );
        }

        return $action->refresh();
    }

    public function makeIdempotencyKey(CasoRecuperacaoReceita $casoRecuperacaoReceita, string $stageName, string $channel): string
    {
        return sha1(sprintf(
            'recovery:%d:%d:%s:%s',
            $casoRecuperacaoReceita->id,
            $casoRecuperacaoReceita->fatura_saas_id ?? 0,
            $stageName,
            $channel
        ));
    }
}
