<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Prometheus\CollectorRegistry;

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
    public function handle(): int
    {
        $subdomain = $this->argument('subdomain');
        $this->info("Iniciando provisionamento para o tenant: {$subdomain}");

        $cliente = Cliente::where('subdominio', $subdomain)->first();

        if (! $cliente) {
            $this->error("Cliente não encontrado com o subdomínio: {$subdomain}");
            $this->recordTenantCreationMetric(false);

            return Command::FAILURE;
        }

        $this->info("Cliente localizado: {$cliente->razao_social}");

        $token = config('services.supabase.access_token');
        $orgId = config('services.supabase.org_id');

        if ($token && $orgId) {
            $this->info('Provisionando novo projeto físico no Supabase...');
            $provider = 'supabase';
            $startTime = microtime(true);

            $dbPass = Str::password(16, true, true, false, false);

            $response = Http::withToken($token)
                ->post('https://api.supabase.com/v1/projects', [
                    'org_id' => $orgId,
                    'name' => 'bx-'.$cliente->subdominio,
                    'db_pass' => $dbPass,
                    'region' => config('services.supabase.region', 'sa-east-1'),
                    'plan' => config('services.supabase.project_plan', 'free'),
                ]);

            if ($response->failed()) {
                $this->error('Falha ao provisionar projeto no Supabase: '.$response->body());
                $this->recordTenantCreationMetric(false, $provider, $startTime);

                return Command::FAILURE;
            }

            $projectData = $response->json();
            $projectRef = $projectData['id'];

            $cliente->supabase_project_ref = $projectRef;
            $cliente->supabase_url = "https://{$projectRef}.supabase.co";
            $cliente->supabase_db_host = "db.{$projectRef}.supabase.co";
            $cliente->supabase_db_password = $dbPass;
            
            // Aguardar alguns segundos antes de buscar as chaves para dar tempo do projeto inicializar (Opcional na v1, mas recomendado)
            $this->info("Buscando chaves de API do projeto {$projectRef}...");
            $keysResponse = Http::withToken($token)
                ->get("https://api.supabase.com/v1/projects/{$projectRef}/api-keys");

            if ($keysResponse->successful()) {
                $keys = $keysResponse->json();
                foreach ($keys as $key) {
                    if ($key['name'] === 'anon') {
                        $cliente->supabase_anon_key = $key['api_key'];
                    } elseif ($key['name'] === 'service_role') {
                        $cliente->supabase_service_role_key = $key['api_key'];
                    }
                }
            } else {
                $this->warn('Não foi possível buscar as chaves de API do Supabase automaticamente.');
            }

            $cliente->save();

            $this->info("Projeto Supabase criado com sucesso! Host: {$cliente->supabase_db_host}");

            $dbName = 'postgres';
            $dbUser = 'postgres';
            $dbHost = $cliente->supabase_db_host;
            $dbPassToUse = $dbPass;

            $this->recordTenantCreationMetric(true, $provider, $startTime);
        } else {
            $this->warn('Credenciais do Supabase não encontradas. Simulando provisionamento com banco local de testes (SQLite).');
            $provider = 'sqlite';
            $startTime = microtime(true);

            $dbPath = database_path("tenant_{$cliente->subdominio}.sqlite");
            if (! file_exists($dbPath)) {
                touch($dbPath);
            }

            $cliente->supabase_db_host = $dbPath;
            $cliente->save();

            $dbName = 'sqlite';
            $dbUser = '';
            $dbHost = '';
            $dbPassToUse = '';
            
            $this->recordTenantCreationMetric(true, $provider, $startTime);
        }

        $this->info('Executando migrações do Tenant...');

        $env = app()->environment();

        if ($env === 'testing' || ! ($token && $orgId)) {
            Config::set('database.connections.tenant', [
                'driver' => 'sqlite',
                'database' => $cliente->supabase_db_host,
                'prefix' => '',
                'foreign_key_constraints' => config('database.connections.sqlite.foreign_key_constraints', true),
            ]);
        } else {
            Config::set('database.connections.tenant', [
                'driver' => 'pgsql',
                'host' => $dbHost,
                'port' => config('database.connections.tenant.port', 5432),
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

        if ($env === 'testing' || ! ($token && $orgId)) {
            $this->warn('Fallback SQLite detectado. As migrations canônicas do tenant exigem PostgreSQL e foram simuladas neste provisionamento local.');
        } else {
            try {
                $this->info('Aguardando 10 segundos antes de tentar rodar as migrations (tempo do Supabase provisionar o banco)...');
                sleep(10);
                
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);
                $this->info(Artisan::output());
            } catch (\Exception $e) {
                $this->error('Erro ao executar migrações. O banco do Supabase pode ainda estar inicializando. Execute: php artisan migrate --database=tenant --path=database/migrations/tenant');
                $this->error('Detalhe do erro: '.$e->getMessage());

                return Command::FAILURE;
            }
        }

        $this->info('Tenant provisionado com sucesso!');

        return Command::SUCCESS;
    }

    protected function recordTenantCreationMetric(bool $success, string $provider = 'unknown', ?float $startTime = null): void
    {
        try {
            $registry = CollectorRegistry::getDefault();
            
            $status = $success ? 'success' : 'failed';
            $counter = $registry->getOrRegisterCounter(
                'app',
                'tenants_created_total',
                'Total de tenants criados',
                ['status', 'provider']
            );
            $counter->inc([$status, $provider]);

            if ($startTime) {
                $duration = microtime(true) - $startTime;
                $histogram = $registry->getOrRegisterHistogram(
                    'app',
                    'tenant_creation_duration_seconds',
                    'Duração da criação do tenant (API ou Local)',
                    ['provider']
                );
                $histogram->observe($duration, [$provider]);
            }
        } catch (\Exception $e) {
            // Ignorar erro do prometheus na CLI
        }
    }
}
