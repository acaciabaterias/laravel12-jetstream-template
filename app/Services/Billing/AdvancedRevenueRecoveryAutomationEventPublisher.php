<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CasoRecuperacaoReceita;
use App\Services\Contracts\Integration\EventPublisherContract;
use App\Services\Integration\IntegrationStorageManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdvancedRevenueRecoveryAutomationEventPublisher
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
        ?CasoRecuperacaoReceita $recoveryCase,
        array $payload,
        array $consumers,
        array $schemaDefinition = [],
    ): void {
        if (! config('advanced_revenue_recovery_automation.events.publish_to_backbone', true)) {
            return;
        }

        if (! $this->hasCentralBackboneTables()) {
            return;
        }

        $tenantExternalRef = $recoveryCase?->cliente?->subdominio ?? 'platform-central';
        $idempotencyKey = sprintf(
            'advanced-recovery:%s:%s',
            strtolower($eventType),
            sha1((string) json_encode($payload))
        );

        $this->integrationStorageManager->using('central', function () use (
            $eventType,
            $payload,
            $tenantExternalRef,
            $idempotencyKey,
            $consumers,
            $schemaDefinition
        ): void {
            $this->eventPublisher->publish(
                eventType: $eventType,
                payload: $payload,
                tenantExternalRef: $tenantExternalRef,
                idempotencyKey: $idempotencyKey,
                correlationId: (string) Str::uuid(),
                eventVersion: 'v1',
                originContext: 'advanced-revenue-recovery-automation',
                metadata: [
                    'consumers' => $consumers,
                    'schema_definition' => $schemaDefinition,
                    'transport_kind' => 'broker',
                    'target' => 'broker:advanced-revenue-recovery-automation',
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
