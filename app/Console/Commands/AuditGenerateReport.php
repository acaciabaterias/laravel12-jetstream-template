<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AuditGenerateReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:generate-report {--output=} {--format=markdown}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera um relatório profissional de auditoria do sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $outputPath = $this->option('output') ?? storage_path('app/audit_report_' . date('Ymd_His') . '.md');
        $this->info("Iniciando geração do relatório em: {$outputPath}");

        $startDate = AuditLog::min('created_at') ?? now();
        $endDate = now();

        $totalEvents = AuditLog::count();
        $totalUsers = AuditLog::distinct('user_id')->count();
        $totalTenants = Cliente::count();

        $statsByAction = AuditLog::select('action', DB::raw('count(*) as total'))
            ->groupBy('action')
            ->pluck('total', 'action')
            ->toArray();

        $topCreatedModels = AuditLog::where('action', 'created')
            ->select('table_name', DB::raw('count(*) as total'))
            ->groupBy('table_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topUsersCreated = AuditLog::where('action', 'created')
            ->with('user')
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topUpdatedModels = AuditLog::where('action', 'updated')
            ->select('table_name', DB::raw('count(*) as total'))
            ->groupBy('table_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $massDeletions = AuditLog::where('action', 'deleted')
            ->select(DB::raw('DATE(created_at) as date'), 'user_id', DB::raw('count(*) as total'))
            ->groupBy('date', 'user_id')
            ->having('total', '>', 50)
            ->with('user')
            ->get();

        $hourFunction = config('database.default') === 'sqlite' 
            ? "CAST(strftime('%H', created_at) AS INTEGER)" 
            : "HOUR(created_at)";

        $suspiciousHours = AuditLog::whereBetween(DB::raw($hourFunction), [22, 6])
            ->with('user')
            ->limit(10)
            ->get();

        $report = "## RELATÓRIO DE AUDITORIA - ERP MULTI-TENANT\n\n";
        $report .= "### 1. EXECUTIVE SUMMARY\n";
        $report .= "- Período auditado: {$startDate} a {$endDate}\n";
        $report .= "- Total de eventos registrados: {$totalEvents}\n";
        $report .= "- Total de usuários ativos: {$totalUsers}\n";
        $report .= "- Total de tenants auditados: {$totalTenants}\n";
        $report .= "- Resumo por tipo de evento:\n";
        foreach ($statsByAction as $action => $count) {
            $report .= "  - {$action}: {$count}\n";
        }

        $report .= "\n### 2. EVENTOS POR CATEGORIA\n\n";
        $report .= "#### 2.1 Criações (created)\n";
        $report .= "- Quantidade total: " . ($statsByAction['created'] ?? 0) . "\n";
        $report .= "- Top 5 models mais criados:\n";
        foreach ($topCreatedModels as $m) $report .= "  - {$m->table_name}: {$m->total}\n";
        $report .= "- Top 5 usuários que mais criaram:\n";
        foreach ($topUsersCreated as $u) $report .= "  - " . ($u->user?->name ?? 'Sistema') . ": {$u->total}\n";

        $report .= "\n#### 2.2 Atualizações (updated)\n";
        $report .= "- Quantidade total: " . ($statsByAction['updated'] ?? 0) . "\n";
        $report .= "- Top 5 models mais alterados:\n";
        foreach ($topUpdatedModels as $m) $report .= "  - {$m->table_name}: {$m->total}\n";

        $report .= "\n### 5. ANOMALIAS E ALERTAS 🚨\n\n";
        if ($massDeletions->isNotEmpty()) {
            $report .= "#### 5.1 Exclusões em Massa\n";
            foreach ($massDeletions as $d) {
                $report .= "  - Alerta: " . ($d->user?->name ?? 'User '.$d->user_id) . " deletou {$d->total} registros em {$d->date}\n";
            }
        } else {
            $report .= "- Nenhuma exclusão em massa detectada.\n";
        }

        $report .= "\n#### 5.2 Acessos Fora de Horário Comercial\n";
        if ($suspiciousHours->isNotEmpty()) {
            foreach ($suspiciousHours as $h) {
                $report .= "  - Suspeito: " . ($h->user?->name ?? 'Sistema') . " em {$h->created_at} (IP: {$h->ip_address})\n";
            }
        } else {
            $report .= "- Nenhum acesso suspeito detectado.\n";
        }

        $report .= "\n### 6. COMPLIANCE E RETENÇÃO 📋\n\n";
        $report .= "- Política de Retenção: Logs mantidos por 90 dias\n";
        $report .= "- Próxima limpeza: " . now()->next(0)->format('Y-m-d') . " (04:00)\n";
        $report .= "- Tamanho aproximado: " . (round($totalEvents * 0.001, 2)) . " MB\n";

        $dir = dirname($outputPath);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::put($outputPath, $report);
        $this->info("Relatório gerado com sucesso em: {$outputPath}");
    }
}
