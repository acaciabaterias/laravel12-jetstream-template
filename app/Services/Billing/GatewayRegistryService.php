<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\GatewayCobrancaSaaS;
use Illuminate\Database\Eloquent\Collection;

class GatewayRegistryService
{
    /**
     * @return Collection<int, GatewayCobrancaSaaS>
     */
    public function activeGateways(): Collection
    {
        return GatewayCobrancaSaaS::query()
            ->where('status', 'active')
            ->orderBy('nome')
            ->get();
    }

    public function findActive(int $gatewayId): GatewayCobrancaSaaS
    {
        return GatewayCobrancaSaaS::query()
            ->whereKey($gatewayId)
            ->where('status', 'active')
            ->firstOrFail();
    }
}
