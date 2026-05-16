<?php

namespace App\Services\Integration;

use App\Models\ContratoEvento;
use Illuminate\Database\Eloquent\Collection;

class EventContractCatalogService
{
    /**
     * @return Collection<int, ContratoEvento>
     */
    public function list(?string $eventType = null): Collection
    {
        return ContratoEvento::query()
            ->when($eventType, fn ($query) => $query->where('event_type', $eventType))
            ->orderBy('event_type')
            ->orderBy('event_version')
            ->get();
    }
}
