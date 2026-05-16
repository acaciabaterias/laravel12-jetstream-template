<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitByTenantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Limpar todos os rate limits antes de cada teste
        // Nota: Como usamos Redis em dev mas SQLite em testes, o comportamento pode variar.
        // O RateLimiter usa o driver de cache padrão (array em testes geralmente).
    }

    public function test_rate_limit_free_plan_allows_60_requests_per_minute(): void
    {
        $tenant = Cliente::factory()->create([
            'subdominio' => 'free-tenant',
            'plano' => 'free',
            'status' => 'active',
        ]);

        $url = 'http://free-tenant.local.local/api/health';

        for ($i = 0; $i < 60; $i++) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $response->assertHeader('X-RateLimit-Limit', 60);
        }

        $response = $this->get($url);
        $response->assertStatus(429);
    }

    public function test_rate_limit_pro_plan_allows_600_requests_per_minute(): void
    {
        $tenant = Cliente::factory()->create([
            'subdominio' => 'pro-tenant',
            'plano' => 'pro',
            'status' => 'active',
        ]);

        $url = 'http://pro-tenant.local.local/api/health';

        // Testamos 61 requisições (acima do limite free)
        for ($i = 0; $i < 61; $i++) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }

        $response->assertHeader('X-RateLimit-Limit', 600);
    }

    public function test_rate_limit_is_isolated_per_tenant(): void
    {
        $tenant1 = Cliente::factory()->create([
            'subdominio' => 'tenant1',
            'plano' => 'free',
            'status' => 'active',
        ]);

        $tenant2 = Cliente::factory()->create([
            'subdominio' => 'tenant2',
            'plano' => 'free',
            'status' => 'active',
        ]);

        // Esgota tenant 1
        for ($i = 0; $i < 60; $i++) {
            $this->get('http://tenant1.local.local/api/health')->assertStatus(200);
        }
        $this->get('http://tenant1.local.local/api/health')->assertStatus(429);

        // Tenant 2 deve continuar funcionando normalmente
        $this->get('http://tenant2.local.local/api/health')->assertStatus(200);
    }

    public function test_reset_command_executes_successfully(): void
    {
        $tenant = Cliente::factory()->create([
            'subdominio' => 'reset-tenant',
            'plano' => 'free',
            'status' => 'active',
        ]);

        // Executa o comando de reset
        $this->artisan('tenant:ratelimit-reset', ['--tenant' => 'reset-tenant'])
            ->assertExitCode(0);
    }
}
