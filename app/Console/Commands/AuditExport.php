<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AuditExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:export {--days=90} {--format=csv} {--output=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporta logs de auditoria para CSV ou JSON';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $format = $this->option('format');
        $outputPath = $this->option('output') ?? storage_path('app/audit_export_'.date('Ymd_His').'.'.$format);

        $this->info("Exportando logs dos últimos {$days} dias para {$format}...");

        $query = AuditLog::where('created_at', '>=', now()->subDays($days))->latest();

        if ($format === 'csv') {
            $this->exportCsv($query, $outputPath);
        } else {
            $this->exportJson($query, $outputPath);
        }

        $this->info("Exportação concluída: {$outputPath}");
    }

    protected function exportCsv($query, $path)
    {
        $file = fopen($path, 'w');
        fputcsv($file, ['ID', 'User ID', 'Action', 'Table', 'Record ID', 'IP', 'Timestamp']);

        $query->chunk(1000, function ($logs) use ($file) {
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user_id,
                    $log->action,
                    $log->table_name,
                    $log->record_id,
                    $log->ip_address,
                    $log->created_at,
                ]);
            }
        });

        fclose($file);
    }

    protected function exportJson($query, $path)
    {
        $data = $query->get()->toArray();
        File::put($path, json_encode($data, JSON_PRETTY_PRINT));
    }
}
