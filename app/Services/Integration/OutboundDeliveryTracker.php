<?php

namespace App\Services\Integration;

use App\Models\EntregaIntegracao;
use App\Models\EventoOutbox;
use App\Support\Integration\IntegrationDirection;
use App\Support\Integration\IntegrationFlowStatus;
use App\Support\Integration\IntegrationTransportKind;
use Carbon\CarbonInterface;

class OutboundDeliveryTracker
{
    public function recordProcessed(
        EventoOutbox $event,
        IntegrationTransportKind $transportKind,
        string $target,
        int $attemptNumber,
        CarbonInterface $startedAt,
        CarbonInterface $finishedAt,
        int $latencyMs,
    ): EntregaIntegracao {
        return EntregaIntegracao::query()->create([
            'entregavel_type' => EventoOutbox::class,
            'entregavel_id' => $event->id,
            'direction' => IntegrationDirection::Outbound,
            'transport_kind' => $transportKind,
            'target' => $target,
            'status' => IntegrationFlowStatus::Processed,
            'attempt_number' => $attemptNumber,
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
            'latency_ms' => $latencyMs,
            'metadata' => [
                'correlation_id' => $event->correlation_id,
            ],
        ]);
    }

    public function recordFailure(
        EventoOutbox $event,
        IntegrationTransportKind $transportKind,
        string $target,
        IntegrationFlowStatus $status,
        int $attemptNumber,
        CarbonInterface $startedAt,
        CarbonInterface $finishedAt,
        string $errorMessage,
    ): EntregaIntegracao {
        return EntregaIntegracao::query()->create([
            'entregavel_type' => EventoOutbox::class,
            'entregavel_id' => $event->id,
            'direction' => IntegrationDirection::Outbound,
            'transport_kind' => $transportKind,
            'target' => $target,
            'status' => $status,
            'attempt_number' => $attemptNumber,
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
            'error_message' => $errorMessage,
            'metadata' => [
                'correlation_id' => $event->correlation_id,
            ],
        ]);
    }
}
