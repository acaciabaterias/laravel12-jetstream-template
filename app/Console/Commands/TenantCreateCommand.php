<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TenantCreateCommand extends Command
{
    protected $signature = 'tenant:create 
                            {--cnpj= : CNPJ do cliente}
                            {--razao= : Razão social}
                            {--email= : Email de contato}
                            {--plano=essential : Plano do cliente}
                            {--subdominio= : Subdomínio (opcional)}';

    protected $description = 'Cria um novo tenant com banco Supabase dedicado';

    public function handle()
    {
        $cnpj = $this->option('cnpj') ?? $this->ask('Qual o CNPJ do cliente?');
        $razao = $this->option('razao') ?? $this->ask('Qual a Razão Social?');
        $email = $this->option('email') ?? $this->ask('Qual o Email de contato?');
        $plano = $this->option('plano');
        $subdominio = $this->option('subdominio') ?? Str::slug($razao);

        // 1. Criar projeto no Supabase via API
        $this->info("Iniciando criação do projeto no Supabase para: {$subdominio}");

        try {
            $supabase = $this->createSupabaseProject($subdominio);

            // 2. Aguardar projeto ficar ativo (polling básico)
            $this->info('Aguardando ativação do projeto (isso pode levar alguns minutos)...');
            $this->waitForProjectReady($supabase['ref']);

            // 3. Rodar migrations no novo banco Supabase
            $this->info('Rodando migrations no banco do tenant...');
            $this->runMigrationsOnTenant($supabase);

            // 4. Criar registro no banco central
            $cliente = Cliente::create([
                'cnpj' => $cnpj,
                'razao_social' => $razao,
                'email_contato' => $email,
                'subdominio' => $subdominio,
                'plano' => $plano,
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
                'supabase_project_ref' => $supabase['ref'],
                'supabase_url' => $supabase['url'],
                'supabase_db_host' => $supabase['db_host'],
                'supabase_db_password' => $supabase['db_password'],
                'supabase_anon_key' => $supabase['anon_key'],
                'supabase_service_role_key' => $supabase['service_role_key'],
                'telefone' => '', // Valor padrão vazio
            ]);

            $this->info('✅ Cliente criado com sucesso!');
            $this->info("Subdomínio: {$cliente->subdominio}.erp.com");
            $this->info("Banco: {$cliente->supabase_db_host}");

        } catch (\Exception $e) {
            $this->error('Falha ao criar tenant: '.$e->getMessage());

            return 1;
        }

        return 0;
    }

    private function createSupabaseProject($subdominio)
    {
        $password = Str::random(16);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.env('SUPABASE_ACCESS_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post('https://api.supabase.com/v1/projects', [
            'name' => "erp_{$subdominio}",
            'region' => env('SUPABASE_REGION', 'sa-east-1'),
            'organization_id' => env('SUPABASE_ORG_ID'),
            'db_pass' => $password,
        ]);

        if ($response->failed()) {
            throw new \Exception('Erro na API do Supabase: '.$response->body());
        }

        $data = $response->json();
        $data['db_password'] = $password;
        $data['db_host'] = "db.{$data['ref']}.supabase.co"; // Formato padrão do host
        $data['url'] = "https://{$data['ref']}.supabase.co";

        return $data;
    }

    private function waitForProjectReady($ref)
    {
        // Implementação simplificada: Polling a cada 10 segundos
        $ready = false;
        $attempts = 0;

        while (! $ready && $attempts < 30) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('SUPABASE_ACCESS_TOKEN'),
            ])->get("https://api.supabase.com/v1/projects/{$ref}");

            if ($response->successful() && $response->json()['status'] === 'ACTIVE_OR_READY') {
                $ready = true;
            } else {
                sleep(10);
                $attempts++;
            }
        }

        if (! $ready) {
            throw new \Exception('Timeout aguardando o projeto Supabase ficar pronto.');
        }
    }

    private function runMigrationsOnTenant($supabase)
    {
        // Configura conexão temporária para o tenant
        config(['database.connections.tenant' => [
            'driver' => 'pgsql',
            'host' => $supabase['db_host'],
            'port' => env('DB_TENANT_PORT', '6543'),
            'database' => 'postgres',
            'username' => 'postgres',
            'password' => $supabase['db_password'],
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ]]);

        // Purge da conexão para aplicar a nova config
        \Illuminate\Support\Facades\DB::purge('tenant');

        // Roda migrations específicas da pasta tenant
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        $this->info(Artisan::output());
    }
}
