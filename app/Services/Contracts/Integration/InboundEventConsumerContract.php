<?php

namespace App\Services\Contracts\Integration;

use App\Models\EventoInbox;

interface InboundEventConsumerContract
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
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
    ): EventoInbox;
}
