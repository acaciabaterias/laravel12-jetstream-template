<?php

namespace App\Services\Integration;

use App\Models\EntregaIntegracao;
use App\Models\EventoInbox;
use App\Services\Contracts\Integration\InboundEventConsumerContract;
use App\Support\Integration\IntegrationDirection;
use App\Support\Integration\IntegrationFlowStatus;
use App\Support\Integration\IntegrationTransportKind;

class InboundEventConsumer implements InboundEventConsumerContract
{
    public function __construct(
        private readonly InboxDeduplicator $inboxDeduplicator,
        private readonly IntegrationMetrics $metrics,
    ) {}

    public function consume(
        string $eventType,
        string $producer,
        array $payload,
        string $tenantExternalRef,
        string $externalEventId,
        string $idempotencyKey,
        string $correlationId,
        string $eventVersion = 'v1',
        ?string $causationId = null,
        array $metadata = [],
    ): EventoInbox {
        $duplicate = $this->inboxDeduplicator->findDuplicate($tenantExternalRef, $externalEventId, $idempotencyKey);

        if ($duplicate) {
            $duplicate->update([
                'duplicate_detected' => true,
                'metadata' => array_merge((array) $duplicate->metadata, [
                    'duplicate_received_at' => now()->toIso8601String(),
                ]),
            ]);

            EntregaIntegracao::query()->create([
                'entregavel_type' => EventoInbox::class,
                'entregavel_id' => $duplicate->id,
                'direction' => IntegrationDirection::Inbound,
                'transport_kind' => IntegrationTransportKind::Broker,
                'target' => $producer,
                'status' => IntegrationFlowStatus::Skipped,
                'attempt_number' => 1,
                'started_at' => now(),
                'finished_at' => now(),
                'metadata' => [
                    'correlation_id' => $correlationId,
                    'external_event_id' => $externalEventId,
                ],
            ]);

            $this->metrics->recordEvent(IntegrationDirection::Inbound, $eventType, IntegrationFlowStatus::Skipped);
            $this->metrics->syncOperationalSnapshot();

            return $duplicate->refresh();
        }

        $inbox = EventoInbox::query()->create([
            'event_type' => $eventType,
            'event_version' => $eventVersion,
            'tenant_external_ref' => $tenantExternalRef,
            'producer' => $producer,
            'correlation_id' => $correlationId,
            'causation_id' => $causationId,
            'external_event_id' => $externalEventId,
            'idempotency_key' => $idempotencyKey,
            'status' => IntegrationFlowStatus::Processed,
            'duplicate_detected' => false,
            'occurred_at' => now(),
            'received_at' => now(),
            'consumed_at' => now(),
            'payload' => $payload,
            'metadata' => $metadata,
        ]);

        EntregaIntegracao::query()->create([
            'entregavel_type' => EventoInbox::class,
            'entregavel_id' => $inbox->id,
            'direction' => IntegrationDirection::Inbound,
            'transport_kind' => IntegrationTransportKind::Broker,
            'target' => $producer,
            'status' => IntegrationFlowStatus::Processed,
            'attempt_number' => 1,
            'started_at' => now(),
            'finished_at' => now(),
            'metadata' => [
                'correlation_id' => $correlationId,
                'external_event_id' => $externalEventId,
            ],
        ]);

        $this->metrics->recordEvent(IntegrationDirection::Inbound, $eventType, IntegrationFlowStatus::Processed);
        $this->metrics->syncOperationalSnapshot();

        return $inbox;
    }
}
