<?php

namespace App\Services\Integration;

use App\Support\Integration\IntegrationFlowStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OutboxEventFactory
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function make(
        string $eventType,
        array $payload,
        string $tenantExternalRef,
        string $idempotencyKey,
        ?string $correlationId = null,
        string $eventVersion = 'v1',
        ?string $originContext = null,
        ?string $causationId = null,
        array $metadata = [],
        ?Carbon $occurredAt = null,
    ): array {
        $timestamp = $occurredAt ?? now();

        return [
            'event_type' => $eventType,
            'event_version' => $eventVersion,
            'tenant_external_ref' => $tenantExternalRef,
            'correlation_id' => $correlationId ?? (string) Str::uuid(),
            'causation_id' => $causationId,
            'idempotency_key' => $idempotencyKey,
            'origin_context' => $originContext,
            'status' => IntegrationFlowStatus::Pending,
            'attempts' => 0,
            'occurred_at' => $timestamp,
            'available_at' => $timestamp,
            'payload' => $payload,
            'metadata' => $metadata,
        ];
    }
}
