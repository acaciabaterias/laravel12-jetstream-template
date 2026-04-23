<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class TenantHealthCommand extends Command
{
    protected $signature = 'tenant:health
        {tenant? : ID, subdominio ou CNPJ do tenant}
        {--json : Exibe o resultado em JSON}';

    protected $description = 'Verifica saúde básica de tenants provisionados';

    public function handle(): int
    {
        $tenants = $this->resolveTenants();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant encontrado para a verificação.');

            return self::FAILURE;
        }

        $rows = $tenants->map(function (Cliente $cliente): array {
            $subscriptionOk = $cliente->hasActiveSubscription() || $cliente->withinTrial();
            $configurationOk = filled($cliente->supabase_db_host) && filled($cliente->supabase_db_password);
            $status = $subscriptionOk && $configurationOk ? 'healthy' : 'warning';

            return [
                'tenant' => $cliente->subdominio,
                'status' => $status,
                'subscription' => $subscriptionOk ? 'ok' : 'attention',
                'database' => $configurationOk ? 'configured' : 'missing',
            ];
        });

        if ((bool) $this->option('json')) {
            $this->line(json_encode($rows->values()->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->table(['Tenant', 'Status', 'Subscription', 'Database'], $rows->all());
        }

        return $rows->contains(fn (array $row): bool => $row['status'] !== 'healthy')
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function resolveTenants(): Collection
    {
        $tenant = $this->argument('tenant');

        if (! is_string($tenant) || $tenant === '') {
            return Cliente::query()->orderBy('id')->get();
        }

        return Cliente::query()
            ->where('id', $tenant)
            ->orWhere('subdominio', $tenant)
            ->orWhere('cnpj', preg_replace('/\D+/', '', $tenant))
            ->get();
    }
}
