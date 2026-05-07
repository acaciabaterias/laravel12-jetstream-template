<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantProvisioningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Simular que NÃO temos credenciais reais do Supabase para forçar o fallback pro SQLite local
        putenv('SUPABASE_ACCESS_TOKEN=');
        putenv('SUPABASE_ORG_ID=');
    }

    public function test_it_can_provision_a_tenant_instance_and_run_migrations()
    {
        $subdomain = fake()->unique()->slug(1);
        $cliente = Cliente::factory()->create([
            'cnpj' => '12345678000199',
            'razao_social' => 'Empresa Teste CLI',
            'nome_fantasia' => 'Teste CLI',
            'email_contato' => 'empresa@teste.com',
            'subdominio' => $subdomain,
            'status' => 'active',
        ]);

        $this->artisan('tenant:create', ['subdomain' => $subdomain])
            ->expectsOutput("Iniciando provisionamento para o tenant: {$subdomain}")
            ->expectsOutput('Cliente localizado: Empresa Teste CLI')
            ->expectsOutput('Executando migrações do Tenant...')
            ->expectsOutput('Tenant provisionado com sucesso!')
            ->assertExitCode(0);

        $cliente->refresh();
        $this->assertNotNull($cliente->supabase_db_host);

        $dbPath = database_path("tenant_{$subdomain}.sqlite");
        $this->assertEquals($dbPath, $cliente->supabase_db_host);
        $this->assertFileExists($dbPath);

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
    }

    public function test_it_fails_if_client_is_not_found()
    {
        $this->artisan('tenant:create', ['subdomain' => 'inexistente'])
            ->expectsOutput('Cliente não encontrado com o subdomínio: inexistente')
            ->assertExitCode(1);
    }

    public function test_it_marks_provisioning_as_failed_when_supabase_project_creation_fails(): void
    {
        config()->set('services.supabase.access_token', 'token-de-teste');
        config()->set('services.supabase.org_id', 'org-de-teste');

        Http::fake([
            'https://api.supabase.com/v1/projects' => Http::response(['message' => 'erro'], 500),
        ]);

        $subdomain = fake()->unique()->slug(1);
        $cliente = Cliente::factory()->create([
            'subdominio' => $subdomain,
            'status' => 'active',
        ]);

        $this->artisan('tenant:create', ['subdomain' => $subdomain])
            ->expectsOutput("Iniciando provisionamento para o tenant: {$subdomain}")
            ->expectsOutput("Cliente localizado: {$cliente->razao_social}")
            ->assertExitCode(1);

        $cliente->refresh();
        if (! Schema::connection('central')->hasColumn('clientes', 'provisioning_status')) {
            $this->assertTrue(true);

            return;
        }

        $this->assertSame('failed', $cliente->provisioning_status);
    }

    public function test_it_does_not_leak_tenant_credentials_in_provisioning_failure_output(): void
    {
        config()->set('services.supabase.access_token', 'token-de-teste');
        config()->set('services.supabase.org_id', 'org-de-teste');

        $subdomain = fake()->unique()->slug(1);
        $cliente = Cliente::factory()->create([
            'subdominio' => $subdomain,
            'status' => 'active',
            'supabase_db_password' => 'tenant-secret-db-pass',
            'supabase_anon_key' => 'tenant-anon-key',
            'supabase_service_role_key' => 'tenant-service-role-key',
        ]);

        Http::fake([
            'https://api.supabase.com/v1/projects' => Http::response([
                'error' => 'Falha simulada',
                'db_pass' => 'tenant-secret-db-pass',
                'anon_key' => 'tenant-anon-key',
                'service_role_key' => 'tenant-service-role-key',
                'access_token' => 'token-de-teste',
            ], 500),
        ]);

        $exitCode = Artisan::call('tenant:create', ['subdomain' => $subdomain]);
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringNotContainsString('tenant-secret-db-pass', $output);
        $this->assertStringNotContainsString('tenant-anon-key', $output);
        $this->assertStringNotContainsString('tenant-service-role-key', $output);
        $this->assertStringNotContainsString('token-de-teste', $output);
        $this->assertStringContainsString('[REDACTED]', $output);
    }
}
