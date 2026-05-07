<?php

namespace App\Services\Contracts\Integration;

use App\Models\EventoOutbox;

interface EventPublisherContract
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
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
    ): EventoOutbox;
}
