<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Services\Contracts\Integration\EventPublisherContract;
use App\Services\Integration\IntegrationStorageManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdvancedWhiteLabelEventPublisher
{
    public function __construct(
        private readonly EventPublisherContract $eventPublisher,
        private readonly IntegrationStorageManager $integrationStorageManager,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $consumers
     */
    public function publish(string $eventType, string $entityFingerprint, array $payload, array $consumers): void
    {
        if (! config('advanced_white_label.events.publish_to_backbone', true)) {
            return;
        }

        if (! $this->hasCentralBackboneTables()) {
            return;
        }

        $this->integrationStorageManager->using('central', function () use ($eventType, $entityFingerprint, $payload, $consumers): void {
            $this->eventPublisher->publish(
                eventType: $eventType,
                payload: $payload,
                tenantExternalRef: (string) ($payload['tenant_subdomain'] ?? 'platform-central'),
                idempotencyKey: sprintf(
                    'advanced-white-label:%s:%s:%s',
                    strtolower($eventType),
                    $entityFingerprint,
                    sha1((string) json_encode($payload))
                ),
                correlationId: (string) Str::uuid(),
                eventVersion: 'v1',
                originContext: 'advanced-white-label',
                metadata: [
                    'consumers' => $consumers,
                    'transport_kind' => 'broker',
                    'target' => 'broker:advanced-white-label',
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
