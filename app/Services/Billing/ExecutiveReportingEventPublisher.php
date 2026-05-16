<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\ExecutiveReportExport;
use App\Services\Contracts\Integration\EventPublisherContract;
use App\Services\Integration\IntegrationStorageManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ExecutiveReportingEventPublisher
{
    public function __construct(
        private readonly EventPublisherContract $eventPublisher,
        private readonly IntegrationStorageManager $integrationStorageManager,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $consumers
     */
    public function publish(string $eventType, ExecutiveReportExport $executiveReportExport, array $payload, array $consumers): void
    {
        if (! config('executive_reporting.events.publish_to_backbone', true)) {
            return;
        }

        if (! $this->hasCentralBackboneTables()) {
            return;
        }

        $this->integrationStorageManager->using('central', function () use ($eventType, $executiveReportExport, $payload, $consumers): void {
            $this->eventPublisher->publish(
                eventType: $eventType,
                payload: $payload,
                tenantExternalRef: 'platform-central',
                idempotencyKey: sprintf(
                    'executive-reporting:%s:%d:%s',
                    strtolower($eventType),
                    $executiveReportExport->id,
                    sha1((string) json_encode($payload))
                ),
                correlationId: (string) Str::uuid(),
                eventVersion: 'v1',
                originContext: 'executive-reporting',
                metadata: [
                    'consumers' => $consumers,
                    'transport_kind' => 'broker',
                    'target' => 'broker:executive-reporting',
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
