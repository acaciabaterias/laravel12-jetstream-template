<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TenantMigrateAllCommand extends Command
{
    protected $signature = 'tenant:migrate-all {--force : Forçar a execução em produção}';

    protected $description = 'Executa as migrações em todos os bancos de dados dos tenants';

    public function handle(): int
    {
        $clientes = Cliente::whereIn('status', ['trial', 'active'])->get();

        if ($clientes->isEmpty()) {
            $this->info('Nenhum cliente ativo para migrar.');

            return 0;
        }

        $this->info('Iniciando migrações para '.$clientes->count().' clientes...');

        foreach ($clientes as $cliente) {
            $this->info('--------------------------------------------------');
            $this->info("Migrando Tenant: {$cliente->subdominio} ({$cliente->razao_social})");

            try {
                config(['database.connections.tenant' => [
                    'driver' => 'pgsql',
                    'host' => $cliente->supabase_db_host,
                    'port' => config('database.connections.tenant.port', '6543'),
                    'database' => config('database.connections.tenant.database', 'postgres'),
                    'username' => config('database.connections.tenant.username', 'postgres'),
                    'password' => $cliente->supabase_db_password,
                    'charset' => 'utf8',
                    'prefix' => '',
                    'schema' => 'public',
                ]]);

                DB::purge('tenant');

                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => $this->option('force'),
                ]);

                $this->info(Artisan::output());
                $this->info("✅ Tenant {$cliente->subdominio} migrado com sucesso.");

            } catch (\Exception $e) {
                $this->error("❌ Falha ao migrar tenant {$cliente->subdominio}: ".$e->getMessage());
            }
        }

        $this->info('--------------------------------------------------');
        $this->info('Processo de migração em massa concluído.');

        return 0;
    }
}
