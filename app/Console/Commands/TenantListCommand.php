<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;

class TenantListCommand extends Command
{
    protected $signature = 'tenant:list
        {--status= : Filtra por status do tenant}
        {--json : Exibe o resultado em JSON}';

    protected $description = 'Lista tenants cadastrados no catálogo central';

    public function handle(): int
    {
        $query = Cliente::query()->orderBy('id');

        if (is_string($this->option('status')) && $this->option('status') !== '') {
            $query->where('status', (string) $this->option('status'));
        }

        $tenants = $query->get(['id', 'razao_social', 'subdominio', 'status', 'plano', 'subscription_ends_at']);

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant encontrado.');

            return self::SUCCESS;
        }

        if ((bool) $this->option('json')) {
            $this->line($tenants->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Razão Social', 'Subdomínio', 'Status', 'Plano', 'Expira em'],
            $tenants->map(fn (Cliente $cliente): array => [
                $cliente->id,
                $cliente->razao_social,
                $cliente->subdominio,
                $cliente->status,
                $cliente->plano,
                optional($cliente->subscription_ends_at)->format('Y-m-d') ?: '-',
            ])->all(),
        );

        return self::SUCCESS;
    }
}
