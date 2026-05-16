<?php

namespace App\Services\Integration;

use Illuminate\Http\Request;

class TenantExternalRefResolver
{
    public function resolve(?Request $request = null): string
    {
        $request ??= request();

        $tenant = $request?->attributes->get('cliente');
        if ($tenant && isset($tenant->subdominio) && filled($tenant->subdominio)) {
            return (string) $tenant->subdominio;
        }

        $tenantHost = (string) config('database.connections.tenant.host');
        if ($tenantHost !== '') {
            return 'tenant-host:'.$tenantHost;
        }

        return 'tenant-default';
    }
}
