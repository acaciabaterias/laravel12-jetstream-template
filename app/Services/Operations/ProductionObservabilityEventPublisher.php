<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\OperationalAlertSnapshot;
use App\Services\Contracts\Integration\EventPublisherContract;
use App\Services\Integration\IntegrationStorageManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductionObservabilityEventPublisher
{
    public function __construct(
        private readonly EventPublisherContract $eventPublisher,
        private readonly IntegrationStorageManager $integrationStorageManager,
    ) {}

    /**
     * @param array<string, mixed> $payload
     * @param array<int, string> $consumers
     * @param array<string, mixed> $schemaDefinition
     */
    public function publish(
        string $eventType,
        OperationalAlertSnapshot $operationalAlertSnapshot,
        array $payload,
        array $consumers,
        array $schemaDefinition = [],
    ): void {
        if (! config('production_observability.events.publish_to_backbone', true)) {
            return;
        }

        if (! $this->hasCentralBackboneTables()) {
            return;
        }

        $correlationId = (string) Str::uuid();
        $idempotencyKey = sprintf(
            'production-observability:%s:%s:%s',
            strtolower($eventType),
            $operationalAlertSnapshot->id,
            sha1(json_encode($payload))
        );

        $this->integrationStorageManager->using('central', function () use ($eventType, $payload, $idempotencyKey, $correlationId, $consumers, $schemaDefinition): void {
            $this->eventPublisher->publish(
                eventType: $eventType,
                payload: $payload,
                tenantExternalRef: 'platform-central',
                idempotencyKey: $idempotencyKey,
                correlationId: $correlationId,
                eventVersion: 'v1',
                originContext: 'production-observability',
                metadata: [
                    'consumers' => $consumers,
                    'schema_definition' => $schemaDefinition,
                    'compatibility_notes' => 'Central operational event published by module 015.',
                    'transport_kind' => 'broker',
                    'target' => 'broker:production-observability',
                ],
            );
        });
    }

    private function hasCentralBackboneTables(): bool
    {
        return Schema::connection('central')->hasTable('contratos_evento')
            && Schema::connection('central')->hasTable('evento_outboxes')
            && Schema::connection('central')->hasTable('entregas_integracao');
    }
}
