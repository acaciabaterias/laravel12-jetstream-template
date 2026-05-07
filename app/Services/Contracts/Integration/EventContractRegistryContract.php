<?php

namespace App\Services\Contracts\Integration;

use App\Models\ContratoEvento;

interface EventContractRegistryContract
{
    /**
     * @param  array<int, string>  $consumers
     * @param  array<string, mixed>|null  $schemaDefinition
     */
    public function register(
        string $eventType,
        string $eventVersion,
        string $producer,
        array $consumers,
        ?array $schemaDefinition = null,
        ?string $compatibilityNotes = null,
    ): ContratoEvento;

    public function find(string $eventType, string $eventVersion = 'v1'): ?ContratoEvento;
}
