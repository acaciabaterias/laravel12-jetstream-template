<?php

namespace App\Services\Integration;

use App\Models\EndpointIntegracao;
use Illuminate\Database\Eloquent\Collection;

class IntegrationGatewayRegistry
{
    /**
     * @return Collection<int, EndpointIntegracao>
     */
    public function list(?string $serviceName = null): Collection
    {
        return EndpointIntegracao::query()
            ->when($serviceName, fn ($query) => $query->where('service_name', $serviceName))
            ->orderBy('service_name')
            ->orderBy('route_name')
            ->get();
    }
}
