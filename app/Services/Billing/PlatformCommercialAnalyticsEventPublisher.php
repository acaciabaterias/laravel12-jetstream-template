<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\SnapshotAnalyticsComercial;
use App\Services\Contracts\Integration\EventPublisherContract;
use App\Services\Integration\IntegrationStorageManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PlatformCommercialAnalyticsEventPublisher
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
        SnapshotAnalyticsComercial $snapshotAnalyticsComercial,
        array $payload,
        array $consumers,
        array $schemaDefinition = [],
    ): void {
        if (! config('platform_commercial_analytics.events.publish_to_backbone', true)) {
            return;
        }

        if (! $this->hasCentralBackboneTables()) {
            return;
        }

        $correlationId = (string) Str::uuid();
        $idempotencyKey = sprintf(
            'commercial-analytics:%s:%s:%s',
            strtolower($eventType),
            $snapshotAnalyticsComercial->id,
            sha1(json_encode($payload))
        );

        $this->integrationStorageManager->using('central', function () use (
            $eventType,
            $payload,
            $idempotencyKey,
            $correlationId,
            $consumers,
            $schemaDefinition
        ): void {
            $this->eventPublisher->publish(
                eventType: $eventType,
                payload: $payload,
                tenantExternalRef: 'platform-central',
                idempotencyKey: $idempotencyKey,
                correlationId: $correlationId,
                eventVersion: 'v1',
                originContext: 'platform-commercial-analytics',
                metadata: [
                    'consumers' => $consumers,
                    'schema_definition' => $schemaDefinition,
                    'compatibility_notes' => 'Central commercial analytics event published by module 014.',
                    'transport_kind' => 'broker',
                    'target' => 'broker:platform-commercial-analytics',
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
