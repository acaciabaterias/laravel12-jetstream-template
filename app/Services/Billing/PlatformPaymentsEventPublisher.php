<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\FaturaSaaS;
use App\Services\Contracts\Integration\EventPublisherContract;
use App\Services\Integration\IntegrationStorageManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PlatformPaymentsEventPublisher
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
        FaturaSaaS $faturaSaaS,
        array $payload,
        array $consumers,
        array $schemaDefinition = [],
    ): void {
        if (! config('platform_payments.events.publish_to_backbone', true)) {
            return;
        }

        if (! $this->hasCentralBackboneTables()) {
            return;
        }

        $tenantExternalRef = (string) $faturaSaaS->cliente->subdominio;
        $correlationId = (string) Str::uuid();
        $idempotencyKey = sprintf(
            'payments:%s:%s:%s',
            strtolower($eventType),
            $faturaSaaS->id,
            sha1(json_encode($payload))
        );

        $this->integrationStorageManager->using('central', function () use (
            $eventType,
            $payload,
            $tenantExternalRef,
            $idempotencyKey,
            $correlationId,
            $consumers,
            $schemaDefinition
        ): void {
            $this->eventPublisher->publish(
                eventType: $eventType,
                payload: $payload,
                tenantExternalRef: $tenantExternalRef,
                idempotencyKey: $idempotencyKey,
                correlationId: $correlationId,
                eventVersion: 'v1',
                originContext: 'platform-payments',
                metadata: [
                    'consumers' => $consumers,
                    'schema_definition' => $schemaDefinition,
                    'compatibility_notes' => 'Central payment event published by module 012.',
                    'transport_kind' => 'broker',
                    'target' => 'broker:platform-payments',
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
