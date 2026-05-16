<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ResetTenantRateLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:ratelimit-reset {--tenant= : O subdomínio do tenant} {--all : Resetar para todos os tenants}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reseta os limites de rate limit de um tenant ou de todos via Redis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $prefix = config('cache.prefix', 'laravel_cache');

        if ($this->option('all')) {
            $pattern = "{$prefix}:tenant:*";
            $this->resetByPattern($pattern);

            return 0;
        }

        $subdominio = $this->option('tenant');

        if (! $subdominio) {
            $this->error('Especifique --tenant=subdominio ou --all');

            return 1;
        }

        $cliente = Cliente::where('subdominio', $subdominio)->first();

        if (! $cliente) {
            $this->error("Tenant '{$subdominio}' não encontrado.");

            return 1;
        }

        $pattern = "{$prefix}:tenant:{$cliente->id}:*";
        $this->resetByPattern($pattern, $subdominio);

        return 0;
    }

    private function resetByPattern(string $pattern, ?string $subdominio = null): void
    {
        try {
            $redis = Redis::connection('cache');
            $keys = $redis->keys($pattern);

            if (empty($keys)) {
                $this->info("Nenhuma chave encontrada para o padrão: {$pattern}");

                return;
            }

            foreach ($keys as $key) {
                // O Redis retorna a chave com o prefixo do banco, mas o comando del precisa da chave relativa se usarmos o facade
                // ou absoluta se usarmos a conexão direta. O facade 'Redis' costuma ser transparente.
                $redis->del($key);
            }

            $count = count($keys);
            $target = $subdominio ? "tenant '{$subdominio}'" : 'todos os tenants';
            $this->info("Sucesso: {$count} chaves de rate limit resetadas para {$target}.");
        } catch (\Throwable $e) {
            $this->error('Erro ao acessar Redis: '.$e->getMessage());
        }
    }
}
