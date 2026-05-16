<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\ExecutiveReportExecutionLog;
use App\Models\ExecutiveReportExport;

class ExecutiveReportingInspectionService
{
    public function __construct(
        private readonly ExecutiveReportingSummaryService $executiveReportingSummaryService,
    ) {}

    /**
     * @param  array{days?:int|string|null,plan?:string|null,channel?:string|null,portfolio?:string|null,recovery_status?:string|null,format?:string|null,status?:string|null,limit?:int|null}  $filters
     * @return array<string, mixed>
     */
    public function inspect(array $filters = []): array
    {
        $normalized = $this->executiveReportingSummaryService->normalizeFilters($filters);
        $snapshot = $this->executiveReportingSummaryService->latestOrCapture($normalized);
        $limit = max(1, min(50, (int) ($filters['limit'] ?? 10)));

        $exportsQuery = ExecutiveReportExport::query()
            ->with(['snapshot', 'definition', 'reexecutedFrom'])
            ->whereHas('snapshot', function ($query) use ($snapshot): void {
                $query->where('filter_hash', $snapshot->filter_hash);
            })
            ->latest('requested_at');

        if (($filters['format'] ?? 'all') !== 'all' && ($filters['format'] ?? null) !== null && $filters['format'] !== '') {
            $exportsQuery->where('format', $filters['format']);
        }

        if (($filters['status'] ?? 'all') !== 'all' && ($filters['status'] ?? null) !== null && $filters['status'] !== '') {
            $exportsQuery->where('export_status', $filters['status']);
        }

        $exports = $exportsQuery->limit($limit)->get();
        $logs = ExecutiveReportExecutionLog::query()
            ->with('reportExport.definition')
            ->whereHas('reportExport.snapshot', function ($query) use ($snapshot): void {
                $query->where('filter_hash', $snapshot->filter_hash);
            })
            ->latest('logged_at')
            ->limit($limit)
            ->get();

        return [
            'summary' => [
                'current_snapshot_id' => $snapshot->id,
                'recent_export_count' => $exports->count(),
                'completed_export_count' => $exports->filter(fn (ExecutiveReportExport $export): bool => $export->export_status->value === 'completed')->count(),
                'failed_export_count' => $exports->filter(fn (ExecutiveReportExport $export): bool => $export->export_status->value === 'failed')->count(),
            ],
            'snapshot' => [
                'id' => $snapshot->id,
                'period_start' => $snapshot->period_start?->toDateString(),
                'period_end' => $snapshot->period_end?->toDateString(),
                'status' => $snapshot->snapshot_status->value,
                'filters' => $snapshot->filter_payload ?? [],
                'kpis' => $this->executiveReportingSummaryService->summarize($snapshot),
                'drilldowns' => $this->executiveReportingSummaryService->drilldowns($snapshot),
            ],
            'exports' => $exports->map(function (ExecutiveReportExport $export): array {
                return [
                    'id' => $export->id,
                    'snapshot_id' => $export->executive_analytics_snapshot_id,
                    'definition_slug' => $export->definition->slug,
                    'format' => $export->format->value,
                    'status' => $export->export_status->value,
                    'scope_summary' => $export->scope_summary,
                    'file_reference' => $export->file_reference,
                    'requested_at' => $export->requested_at?->toIso8601String(),
                    'completed_at' => $export->completed_at?->toIso8601String(),
                    'reexecuted_from_export_id' => $export->reexecuted_from_export_id,
                ];
            })->values()->all(),
            'execution_logs' => $logs->map(function (ExecutiveReportExecutionLog $log): array {
                return [
                    'id' => $log->id,
                    'export_id' => $log->executive_report_export_id,
                    'event_type' => $log->event_type,
                    'operator_name' => $log->operator_name,
                    'logged_at' => $log->logged_at?->toIso8601String(),
                    'payload' => $log->event_payload ?? [],
                ];
            })->values()->all(),
        ];
    }
}
