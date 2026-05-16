<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class AuditCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:cleanup {--days=90}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove logs de auditoria antigos para economizar espaço em disco';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $date = now()->subDays($days);

        $count = AuditLog::where('created_at', '<', $date)->delete();

        $this->info("Limpeza concluída. {$count} registros de auditoria com mais de {$days} dias foram removidos.");
    }
}
