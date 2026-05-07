<?php

namespace App\Services\Integration;

use App\Jobs\DispatchOutboxEventJob;
use App\Models\EventoOutbox;
use App\Services\Contracts\Integration\EventPublisherContract;
use Illuminate\Support\Facades\DB;

class EventPublisher implements EventPublisherContract
{
    public function __construct(
        private readonly EventContractRegistry $contractRegistry,
        private readonly OutboxEventFactory $outboxEventFactory,
    ) {}

    public function publish(
        string $eventType,
        array $payload,
        string $tenantExternalRef,
        string $idempotencyKey,
        string $correlationId,
        string $eventVersion = 'v1',
        ?string $originContext = null,
        ?string $causationId = null,
        array $metadata = [],
    ): EventoOutbox {
        $contract = $this->contractRegistry->find($eventType, $eventVersion);

        if (! $contract) {
            $contract = $this->contractRegistry->register(
                eventType: $eventType,
                eventVersion: $eventVersion,
                producer: $originContext ?? 'erp-core',
                consumers: (array) ($metadata['consumers'] ?? []),
                schemaDefinition: isset($metadata['schema_definition']) && is_array($metadata['schema_definition'])
                    ? $metadata['schema_definition']
                    : null,
                compatibilityNotes: $metadata['compatibility_notes'] ?? null,
            );
        }

        $outbox = EventoOutbox::query()->create(
            $this->outboxEventFactory->make(
                eventType: $eventType,
                payload: $payload,
                tenantExternalRef: $tenantExternalRef,
                idempotencyKey: $idempotencyKey,
                correlationId: $correlationId,
                eventVersion: $eventVersion,
                originContext: $originContext ?? $contract->producer,
                causationId: $causationId,
                metadata: array_merge($metadata, [
                    'contract_id' => $contract->id,
                ]),
            )
        );

        DB::afterCommit(function () use ($outbox): void {
            DispatchOutboxEventJob::dispatch($outbox->id)
                ->onQueue(config('services.integration_backbone.broker.outbox_queue'));
        });

        return $outbox;
    }
}
