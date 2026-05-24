<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use Database\Seeders\LoadTestTenantSeeder;
use Tests\TestCase;

class LoadTestTenantSeederTest extends TestCase
{
    public function test_load_test_tenant_seeder_creates_tenant_aware_hosts_with_active_enterprise_plan(): void
    {
        $this->seed(LoadTestTenantSeeder::class);

        $tenant = Cliente::query()->where('subdominio', 'loadtest-001')->first();

        $this->assertNotNull($tenant);
        $this->assertSame('enterprise', $tenant->plano);
        $this->assertSame('active', $tenant->status);
        $this->assertSame('tenant-shared-loadtest.pgsql.local', $tenant->supabase_db_host);
        $this->assertStringContainsString('https://loadtest-001.erp.local', (string) $tenant->supabase_url);
        $this->assertSame('loadtest-shared-password', $tenant->supabase_db_password);

        $anonKey = $tenant->supabase_anon_key;
        $serviceRoleKey = $tenant->supabase_service_role_key;

        $this->seed(LoadTestTenantSeeder::class);

        $tenant->refresh();

        $this->assertSame($anonKey, $tenant->supabase_anon_key);
        $this->assertSame($serviceRoleKey, $tenant->supabase_service_role_key);
        $this->assertSame(100, Cliente::query()->where('subdominio', 'like', 'loadtest-%')->count());
    }
}
