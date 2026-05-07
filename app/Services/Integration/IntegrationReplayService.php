<?php

namespace App\Services\Integration;

use App\Jobs\LogAuditJob;
use App\Models\EntregaIntegracao;
use App\Models\EventoInbox;
use App\Models\EventoOutbox;
use App\Models\User;
use App\Services\Contracts\Integration\IntegrationReplayServiceContract;
use App\Support\Integration\IntegrationFlowStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class IntegrationReplayService implements IntegrationReplayServiceContract
{
    public function __construct(private readonly IntegrationMetrics $metrics) {}

    public function replay(EntregaIntegracao $delivery, User $operator, array $context = []): EntregaIntegracao
    {
        if (! Gate::forUser($operator)->check('replay-integration-events')) {
            throw new AuthorizationException('Usuário não autorizado a reprocessar integrações.');
        }

        if (! in_array($delivery->status, [IntegrationFlowStatus::Failed, IntegrationFlowStatus::DeadLetter], true)) {
            throw new RuntimeException('Apenas entregas com falha ou dead-letter podem ser reenfileiradas.');
        }

        $deliverable = $delivery->entregavel;
        if (! $deliverable instanceof EventoOutbox && ! $deliverable instanceof EventoInbox) {
            throw new RuntimeException('Tipo de entrega não suportado para replay.');
        }

        if ($deliverable instanceof EventoOutbox) {
            $deliverable->update([
                'status' => IntegrationFlowStatus::Pending,
                'available_at' => now(),
                'dispatched_at' => null,
                'last_error' => null,
                'metadata' => array_merge((array) $deliverable->metadata, [
                    'replay_requested_by' => $operator->id,
                    'replay_requested_at' => now()->toIso8601String(),
                ]),
            ]);
        }

        if ($deliverable instanceof EventoInbox) {
            $deliverable->update([
                'status' => IntegrationFlowStatus::Pending,
                'consumed_at' => null,
                'last_error' => null,
                'duplicate_detected' => false,
                'metadata' => array_merge((array) $deliverable->metadata, [
                    'replay_requested_by' => $operator->id,
                    'replay_requested_at' => now()->toIso8601String(),
                ]),
            ]);
        }

        $replayDelivery = EntregaIntegracao::query()->create([
            'entregavel_type' => $delivery->entregavel_type,
            'entregavel_id' => $delivery->entregavel_id,
            'direction' => $delivery->direction,
            'transport_kind' => $delivery->transport_kind,
            'target' => $delivery->target,
            'status' => IntegrationFlowStatus::Replayed,
            'attempt_number' => $delivery->attempt_number + 1,
            'replayed_from_entrega_id' => $delivery->id,
            'started_at' => now(),
            'finished_at' => now(),
            'metadata' => array_merge((array) $delivery->metadata, $context, [
                'operator_id' => $operator->id,
            ]),
        ]);

        $this->metrics->recordReplay($delivery->target, IntegrationFlowStatus::Replayed);
        $this->metrics->syncOperationalSnapshot();
        $this->recordReplayAudit($delivery, $replayDelivery, $operator, $context);

        return $replayDelivery;
    }

    private function recordReplayAudit(
        EntregaIntegracao $originalDelivery,
        EntregaIntegracao $replayDelivery,
        User $operator,
        array $context
    ): void {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        LogAuditJob::dispatchSync([
            'user_id' => $operator->id,
            'action' => 'replayed',
            'table_name' => 'entregas_integracao',
            'record_id' => $originalDelivery->id,
            'old_values' => [
                'status' => $originalDelivery->status->value,
                'attempt_number' => $originalDelivery->attempt_number,
                'target' => $originalDelivery->target,
            ],
            'new_values' => [
                'status' => $replayDelivery->status->value,
                'replay_delivery_id' => $replayDelivery->id,
                'replayed_from_entrega_id' => $originalDelivery->id,
                'operator_id' => $operator->id,
                'reason' => $context['reason'] ?? null,
                'source' => $context['source'] ?? 'manual',
            ],
        ]);
    }
}
