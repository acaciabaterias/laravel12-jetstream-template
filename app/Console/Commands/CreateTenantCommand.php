<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CreateTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {subdomain : O subdomínio do cliente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provisiona um novo tenant (Banco de Dados) via Supabase e roda as migrações';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        $this->info("Iniciando provisionamento para o tenant: {$subdomain}");

        $cliente = Cliente::where('subdominio', $subdomain)->first();

        if (!$cliente) {
            $this->error("Cliente não encontrado com o subdomínio: {$subdomain}");
            return Command::FAILURE;
        }

        $this->info("Cliente localizado: {$cliente->razao_social}");

        $token = env('SUPABASE_ACCESS_TOKEN');
        $orgId = env('SUPABASE_ORG_ID');

        // Se tivermos as credenciais do Supabase, criamos o projeto real
        if ($token && $orgId) {
            $this->info("Provisionando novo projeto físico no Supabase...");
            
            $dbPass = Str::password(16, true, true, false, false);
            
            // 1. Criar Projeto no Supabase
            $response = Http::withToken($token)
                ->post('https://api.supabase.com/v1/projects', [
                    'org_id' => $orgId,
                    'name' => 'bx-' . $cliente->subdominio,
                    'db_pass' => $dbPass,
                    'region' => env('SUPABASE_REGION', 'sa-east-1'),
                    'plan' => 'free' // Em produção dinâmico dependendo do plano do cliente
                ]);

            if ($response->failed()) {
                $this->error("Falha ao provisionar projeto no Supabase: " . $response->body());
                return Command::FAILURE;
            }

            $projectData = $response->json();
            
            // 2. Salvar metadados no cliente
            $cliente->supabase_host = "db." . $projectData['id'] . ".supabase.co";
            // Nota: Numa aplicação real, criptografar e proteger bem esta senha
            $cliente->supabase_password = encrypt($dbPass); 
            $cliente->save();

            $this->info("Projeto Supabase criado com sucesso! Host: {$cliente->supabase_host}");
            
            $dbName = 'postgres';
            $dbUser = 'postgres';
            $dbHost = $cliente->supabase_host;
            $dbPassToUse = $dbPass;

        } else {
            $this->warn("Credenciais do Supabase não encontradas. Simulando provisionamento com banco local de testes (SQLite).");
            
            // Fallback para testes/local
            $dbPath = database_path("tenant_{$cliente->subdominio}.sqlite");
            if (!file_exists($dbPath)) {
                touch($dbPath);
            }
            
            $cliente->supabase_host = $dbPath; // Para testes, vamos usar o host como o path
            $cliente->save();
            
            $dbName = 'sqlite';
            $dbUser = '';
            $dbHost = '';
            $dbPassToUse = '';
        }

        // 3. Rodar migrações do Tenant
        $this->info("Executando migrações do Tenant...");
        
        $env = app()->environment();
        
        if ($env === 'testing' || !($token && $orgId)) {
             Config::set('database.connections.tenant', [
                'driver' => 'sqlite',
                'database' => $cliente->supabase_host,
                'prefix' => '',
                'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            ]);
        } else {
            Config::set('database.connections.tenant', [
                'driver' => 'pgsql',
                'host' => $dbHost,
                'port' => 5432,
                'database' => $dbName,
                'username' => $dbUser,
                'password' => $dbPassToUse,
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
                'sslmode' => 'require',
            ]);
        }

        DB::purge('tenant');

        if (app()->environment() !== 'testing') {
            try {
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);
                $this->info(Artisan::output());
            } catch (\Exception $e) {
                $this->error("Erro ao executar migrações: " . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            $this->info("Ambiente de testes detectado, pulando migrações reais do banco");
        }

        $this->info("Tenant provisionado com sucesso!");
        
        return Command::SUCCESS;
    }
}
