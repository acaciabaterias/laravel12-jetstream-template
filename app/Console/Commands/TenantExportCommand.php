<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;

class TenantExportCommand extends Command
{
    protected $signature = 'tenant:export {cliente_id} {--format=sql}';

    protected $description = 'Exporta os dados (dump) de um tenant específico';

    public function handle()
    {
        $cliente = Cliente::find($this->argument('cliente_id'));

        if (! $cliente) {
            $this->error('Cliente não encontrado.');

            return 1;
        }

        $this->info("Iniciando exportação de dados para: {$cliente->razao_social}");

        $filename = "backups/tenant_{$cliente->subdominio}_".now()->format('Y-m-d_His').'.sql';
        $path = storage_path("app/{$filename}");

        // Nota: O Supabase não expõe pg_dump via API diretamente da forma que o snippet sugeria sem ferramentas externas.
        // Simulando a lógica de exportação via pg_dump usando as credenciais do cliente.
        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s %s > %s',
            escapeshellarg($cliente->supabase_db_password),
            escapeshellarg($cliente->supabase_db_host),
            '6543',
            'postgres',
            'postgres',
            escapeshellarg($path)
        );

        $this->info("Executando: {$command}");

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error('Falha ao executar pg_dump. Verifique se a ferramenta está instalada e o host está acessível.');

            return 1;
        }

        $this->info('✅ Exportação concluída com sucesso!');
        $this->info("Arquivo salvo em: storage/app/{$filename}");

        return 0;
    }
}
