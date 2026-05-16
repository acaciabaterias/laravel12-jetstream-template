<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class AuditView extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:view {--limit=20} {--user=} {--action=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Visualiza os logs de auditoria mais recentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = AuditLog::with('user')->latest();

        if ($this->option('user')) {
            $query->where('user_id', $this->option('user'));
        }

        if ($this->option('action')) {
            $query->where('action', $this->option('action'));
        }

        $logs = $query->limit((int) $this->option('limit'))->get();

        if ($logs->isEmpty()) {
            $this->info('Nenhum log de auditoria encontrado.');

            return;
        }

        $this->table(
            ['ID', 'Usuário', 'Ação', 'Tabela', 'ID Reg.', 'Data/Hora'],
            $logs->map(fn ($log) => [
                $log->id,
                $log->user?->name ?? 'Sistema',
                $log->action,
                $log->table_name,
                $log->record_id,
                $log->created_at->toDateTimeString(),
            ])
        );
    }
}
