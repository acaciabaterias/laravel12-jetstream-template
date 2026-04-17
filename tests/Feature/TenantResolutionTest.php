<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Tests\TestCase;

class TenantResolutionTest extends TestCase
{
    public function test_subdomain_is_resolved_to_client_and_tenant_connection_is_configured(): void
    {
        $cliente = Cliente::factory()->create([
            'subdominio' => 'test-tenant',
            'status' => 'active',
            'supabase_db_host' => 'remote-db.supabase.co',
            'supabase_db_password' => 'secret-pass',
        ]);

        $response = $this->get('http://test-tenant.erp.com/');

        // O teste vai tentar conectar ao Supabase real se não mockarmos o DB::purge
        // Mas podemos verificar se a config foi alterada
        $this->assertEquals('remote-db.supabase.co', config('database.connections.tenant.host'));
        $this->assertEquals('secret-pass', config('database.connections.tenant.password'));
    }

    public function test_unknown_subdomain_returns_404(): void
    {
        $response = $this->get('http://unknown.erp.com/');

        $response->assertStatus(404);
    }

    public function test_inactive_client_returns_402(): void
    {
        $cliente = Cliente::factory()->create([
            'subdominio' => 'expired-tenant',
            'status' => 'expired',
        ]);

        $response = $this->get('http://expired-tenant.erp.com/');

        $response->assertStatus(402);
    }
}
