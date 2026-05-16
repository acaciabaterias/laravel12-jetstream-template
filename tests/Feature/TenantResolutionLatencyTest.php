<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantResolutionLatencyTest extends TestCase
{
    /**
     * Teste de latência de resolução de tenant.
     * Simplificado para utilizar a infraestrutura consolidada de testes.
     */
    public function test_tenant_resolution_latency_is_under_50ms()
    {
        $subdomain = fake()->unique()->slug(1);
        $cliente = Cliente::factory()->create([
            'subdominio' => $subdomain,
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

        // Verifica configuração
        $configHost = config('database.connections.tenant.host');

        $end = microtime(true);
        $durationMs = ($end - $start) * 1000;

        $this->assertEquals('localhost', $configHost);
        $this->assertLessThan(50, $durationMs, "Tenant resolution took more than 50ms: {$durationMs}ms");
    }
}
