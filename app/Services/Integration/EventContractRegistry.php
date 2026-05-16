<?php

namespace App\Services\Integration;

use App\Models\ContratoEvento;
use App\Services\Contracts\Integration\EventContractRegistryContract;

class EventContractRegistry implements EventContractRegistryContract
{
    public function register(
        string $eventType,
        string $eventVersion,
        string $producer,
        array $consumers,
        ?array $schemaDefinition = null,
        ?string $compatibilityNotes = null,
    ): ContratoEvento {
        return ContratoEvento::query()->updateOrCreate(
            [
                'event_type' => $eventType,
                'event_version' => $eventVersion,
            ],
            [
                'producer' => $producer,
                'status' => 'active',
                'consumers' => array_values($consumers),
                'schema_definition' => $schemaDefinition,
                'compatibility_notes' => $compatibilityNotes,
            ]
        );
    }

    public function find(string $eventType, string $eventVersion = 'v1'): ?ContratoEvento
    {
        return ContratoEvento::query()
            ->where('event_type', $eventType)
            ->where('event_version', $eventVersion)
            ->first();
    }
}
