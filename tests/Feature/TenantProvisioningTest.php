<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
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
        // 1. Criar um cliente base
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
            ->expectsOutput("Cliente localizado: Empresa Teste CLI")
            ->expectsOutput("Executando migrações do Tenant...")
            ->expectsOutput("Tenant provisionado com sucesso!")
            ->assertExitCode(0);

        // 2. Verificar se o cliente foi atualizado com a credencial (caminho SQLite fallback no teste local)
        $cliente->refresh();
        $this->assertNotNull($cliente->supabase_db_host);
        
        $dbPath = database_path("tenant_{$subdomain}.sqlite");
        $this->assertEquals($dbPath, $cliente->supabase_db_host);
        $this->assertFileExists($dbPath);

        // Limpar o arquivo criado para esse teste
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
    }

    public function test_it_fails_if_client_is_not_found()
    {
        $this->artisan('tenant:create', ['subdomain' => 'inexistente'])
            ->expectsOutput("Cliente não encontrado com o subdomínio: inexistente")
            ->assertExitCode(1);
    }
}
