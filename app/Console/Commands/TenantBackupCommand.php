<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class TenantBackupCommand extends Command
{
    protected $signature = 'tenant:backup
        {tenant : ID, subdominio ou CNPJ do tenant}
        {--path= : Diretório de destino do backup}
        {--format=sql : Formato do backup}
        {--pretend : Apenas exibe o comando sem executar}';

    protected $description = 'Gera backup do banco de um tenant específico';

    public function handle(): int
    {
        $cliente = $this->resolveTenant((string) $this->argument('tenant'));

        if (! $cliente instanceof Cliente) {
            $this->error('Tenant não encontrado.');

            return self::FAILURE;
        }

        $directory = (string) ($this->option('path') ?: storage_path('app/backups'));
        File::ensureDirectoryExists($directory);

        $timestamp = now()->format('Ymd_His');
        $format = (string) $this->option('format');
        $filename = sprintf('tenant_%s_%s.%s', $cliente->subdominio, $timestamp, $format);
        $targetPath = rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename;

        if ($cliente->supabase_db_host && File::exists($cliente->supabase_db_host)) {
            File::copy($cliente->supabase_db_host, $targetPath);
            $this->info("Backup local copiado para {$targetPath}");

            return self::SUCCESS;
        }

        $command = $this->buildPgDumpCommand($cliente, $targetPath);

        if ((bool) $this->option('pretend')) {
            $this->line($command);

            return self::SUCCESS;
        }

        if (! $this->pgDumpAvailable()) {
            $this->error('pg_dump não está disponível no ambiente atual.');

            return self::FAILURE;
        }

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error(trim($process->getErrorOutput()) ?: 'Falha ao gerar backup do tenant.');

            return self::FAILURE;
        }

        $this->info("Backup gerado em {$targetPath}");

        return self::SUCCESS;
    }

    private function resolveTenant(string $tenant): ?Cliente
    {
        return Cliente::query()
            ->where('id', $tenant)
            ->orWhere('subdominio', $tenant)
            ->orWhere('cnpj', preg_replace('/\D+/', '', $tenant))
            ->first();
    }

    private function buildPgDumpCommand(Cliente $cliente, string $targetPath): string
    {
        $host = escapeshellarg((string) $cliente->supabase_db_host);
        $password = escapeshellarg((string) $cliente->supabase_db_password);
        $database = escapeshellarg((string) config('database.connections.tenant.database', 'postgres'));
        $port = escapeshellarg((string) config('database.connections.tenant.port', '6543'));
        $username = escapeshellarg((string) config('database.connections.tenant.username', 'postgres'));
        $output = escapeshellarg($targetPath);

        return "PGPASSWORD={$password} pg_dump -h {$host} -p {$port} -U {$username} {$database} > {$output}";
    }

    private function pgDumpAvailable(): bool
    {
        $process = Process::fromShellCommandline('command -v pg_dump');
        $process->run();

        return $process->isSuccessful();
    }
}
