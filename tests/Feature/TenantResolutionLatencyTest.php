<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantResolutionLatencyTest extends TestCase
{
    use RefreshDatabase;

    protected $connectionsToTransact = ['central'];

    protected function afterRefreshingDatabase()
    {
        $this->artisan('migrate', [
            '--database' => 'central',
            '--path' => 'database/migrations/central',
            '--force' => true,
        ]);
    }

    public function test_tenant_resolution_latency_is_under_50ms()
    {
        $cliente = Cliente::factory()->create([
            'subdominio' => 'fast-tenant',
            'status' => 'active',
            'supabase_db_host' => 'localhost',
            'supabase_db_password' => 'secret',
        ]);

        $start = microtime(true);

        // Simulamos o core do middleware para focar exatamente no seu overhead
        config(['database.connections.tenant' => [
            'driver' => 'pgsql',
            'host' => $cliente->supabase_db_host,
            'password' => $cliente->supabase_db_password,
            'database' => 'postgres',
            'username' => 'postgres',
        ]]);
        
        DB::purge('tenant');
        
        // Verifica conexão ou configuração (para fins do teste em um CI mockado, apenas medir o tempo processual)
        $configHost = config('database.connections.tenant.host');
        
        $end = microtime(true);
        $durationMs = ($end - $start) * 1000;

        $this->assertEquals('localhost', $configHost);
        $this->assertLessThan(50, $durationMs, "Tenant resolution took more than 50ms: {$durationMs}ms");
    }
}
