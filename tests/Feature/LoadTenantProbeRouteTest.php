<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use Tests\TestCase;

class LoadTenantProbeRouteTest extends TestCase
{
    public function test_load_tenant_probe_resolves_the_customer_by_subdomain(): void
    {
        $cliente = Cliente::factory()->create([
            'subdominio' => 'loadtest-001',
            'status' => 'active',
            'supabase_db_host' => 'tenant-shared-loadtest.pgsql.local',
            'supabase_db_password' => 'loadtest-shared-password',
        ]);

        $response = $this->get('http://loadtest-001.erp.local/load/tenant-probe');

        $tenantUsesSharedPgBaseline = config('database.connections.tenant.driver') === 'pgsql'
            && filled((string) config('database.connections.tenant.host'));

        $response
            ->assertOk()
            ->assertJson([
                'tenant_host' => $tenantUsesSharedPgBaseline
                    ? config('database.connections.tenant.host')
                    : 'tenant-shared-loadtest.pgsql.local',
                'cliente_id' => $cliente->id,
                'subdominio' => 'loadtest-001',
            ]);
    }
}
