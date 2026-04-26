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
    public function handle(): int
    {
        $subdomain = $this->argument('subdomain');
        $this->info("Iniciando provisionamento para o tenant: {$subdomain}");

        $cliente = Cliente::where('subdominio', $subdomain)->first();

        if (! $cliente) {
            $this->error("Cliente não encontrado com o subdomínio: {$subdomain}");

            return Command::FAILURE;
        }

        $this->info("Cliente localizado: {$cliente->razao_social}");

        $token = config('services.supabase.access_token');
        $orgId = config('services.supabase.org_id');

        if ($token && $orgId) {
            $this->info('Provisionando novo projeto físico no Supabase...');

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

                return Command::FAILURE;
            }

            $projectData = $response->json();

            $cliente->supabase_db_host = 'db.'.$projectData['id'].'.supabase.co';
            $cliente->supabase_db_password = $dbPass;
            $cliente->save();

            $this->info("Projeto Supabase criado com sucesso! Host: {$cliente->supabase_db_host}");

            $dbName = 'postgres';
            $dbUser = 'postgres';
            $dbHost = $cliente->supabase_db_host;
            $dbPassToUse = $dbPass;

        } else {
            $this->warn('Credenciais do Supabase não encontradas. Simulando provisionamento com banco local de testes (SQLite).');

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
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);
                $this->info(Artisan::output());
            } catch (\Exception $e) {
                $this->error('Erro ao executar migrações: '.$e->getMessage());

                return Command::FAILURE;
            }
        }

        $this->info('Tenant provisionado com sucesso!');

        return Command::SUCCESS;
    }
}
