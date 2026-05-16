<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\LoadTestBaseline;
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
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $consumers
     * @param  array<string, mixed>  $schemaDefinition
     */
    public function publish(
        string $eventType,
        OperationalAlertSnapshot $operationalAlertSnapshot,
        array $payload,
        array $consumers,
        array $schemaDefinition = [],
    ): void {
        $this->publishEvent(
            eventType: $eventType,
            entityFingerprint: sprintf('snapshot:%s', $operationalAlertSnapshot->id),
            payload: $payload,
            consumers: $consumers,
            schemaDefinition: $schemaDefinition,
        );
    }

    public function publishBaselineUpdated(LoadTestBaseline $loadTestBaseline): void
    {
        $this->publishEvent(
            eventType: 'BASELINE_CARGA_ATUALIZADO',
            entityFingerprint: sprintf('baseline:%s', $loadTestBaseline->id),
            payload: [
                'baseline_id' => $loadTestBaseline->id,
                'scenario_name' => $loadTestBaseline->scenario_name,
                'flow_name' => $loadTestBaseline->flow_name,
                'throughput_per_minute' => $loadTestBaseline->throughput_per_minute,
                'p95_latency_ms' => $loadTestBaseline->p95_latency_ms,
                'error_rate' => round((float) $loadTestBaseline->error_rate, 4),
                'accepted_at' => $loadTestBaseline->accepted_at?->toAtomString(),
            ],
            consumers: ['platform'],
            schemaDefinition: [
                'baseline_id' => 'integer',
                'scenario_name' => 'string',
                'flow_name' => 'string',
                'throughput_per_minute' => 'integer',
                'p95_latency_ms' => 'integer',
                'error_rate' => 'float',
                'accepted_at' => 'string',
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $consumers
     * @param  array<string, mixed>  $schemaDefinition
     */
    private function publishEvent(
        string $eventType,
        string $entityFingerprint,
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
            $entityFingerprint,
            sha1((string) json_encode($payload))
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
