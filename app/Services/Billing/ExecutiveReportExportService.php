<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\ExecutiveAnalyticsSnapshot;
use App\Models\ExecutiveReportDefinition;
use App\Models\ExecutiveReportExecutionLog;
use App\Models\ExecutiveReportExport;
use App\Models\UsuarioPlataforma;
use App\Support\Billing\ExecutiveAnalyticsSnapshotStatus;
use App\Support\Billing\ExecutiveReportExecutionClassifier;
use App\Support\Billing\ExecutiveReportExportStatus;
use App\Support\Billing\ExecutiveReportFormat;
use InvalidArgumentException;
use Throwable;

class ExecutiveReportExportService
{
    public function __construct(
        private readonly ExecutiveReportingSummaryService $executiveReportingSummaryService,
        private readonly ExecutiveReportArtifactService $executiveReportArtifactService,
        private readonly ExecutiveReportingEventPublisher $executiveReportingEventPublisher,
        private readonly ExecutiveReportExecutionClassifier $executiveReportExecutionClassifier,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters, string $format, ?int $requestedBy = null): ExecutiveReportExport
    {
        $snapshot = $this->executiveReportingSummaryService->latestOrCapture($filters, $requestedBy);

        return $this->persistExport($snapshot, ExecutiveReportFormat::from($format), $requestedBy);
    }

    public function reexecute(ExecutiveReportExport $executiveReportExport, ?int $requestedBy = null): ExecutiveReportExport
    {
        return $this->persistExport($executiveReportExport->snapshot, $executiveReportExport->format, $requestedBy, $executiveReportExport);
    }

    public function resolveFinalStatus(bool $isReexecution): ExecutiveReportExportStatus
    {
        return $this->executiveReportExecutionClassifier->statusFor($isReexecution);
    }

    private function persistExport(
        ExecutiveAnalyticsSnapshot $executiveAnalyticsSnapshot,
        ExecutiveReportFormat $executiveReportFormat,
        ?int $requestedBy = null,
        ?ExecutiveReportExport $reexecutedFrom = null,
    ): ExecutiveReportExport {
        if ($executiveAnalyticsSnapshot->snapshot_status !== ExecutiveAnalyticsSnapshotStatus::Ready) {
            throw new InvalidArgumentException('Snapshot executivo incompleto para exportacao.');
        }

        $definition = $this->resolveDefinition($requestedBy);
        $operator = $requestedBy ? UsuarioPlataforma::query()->find($requestedBy) : null;

        $export = ExecutiveReportExport::query()->create([
            'executive_analytics_snapshot_id' => $executiveAnalyticsSnapshot->id,
            'executive_report_definition_id' => $definition->id,
            'reexecuted_from_export_id' => $reexecutedFrom?->id,
            'format' => $executiveReportFormat->value,
            'export_status' => ExecutiveReportExportStatus::Pending->value,
            'requested_by' => $requestedBy,
            'requested_at' => now(),
            'scope_summary' => $this->scopeSummary($executiveAnalyticsSnapshot),
            'metadata' => ['filters' => $executiveAnalyticsSnapshot->filter_payload ?? []],
        ]);

        $this->logEvent($export, 'requested', $operator);

        try {
            $relativePath = $this->executiveReportArtifactService->generate($export, $executiveAnalyticsSnapshot, $definition);
            $completionEvent = $this->executiveReportExecutionClassifier->completionEventFor($reexecutedFrom !== null);

            $export->forceFill([
                'file_reference' => $relativePath,
                'export_status' => $this->resolveFinalStatus($reexecutedFrom !== null)->value,
                'completed_at' => now(),
            ])->save();

            $this->logEvent($export, $completionEvent, $operator);
            $this->publishCompletionEvent($export, $completionEvent);

            return $export->fresh(['snapshot', 'definition', 'executionLogs']) ?? $export;
        } catch (Throwable $throwable) {
            $export->forceFill([
                'export_status' => ExecutiveReportExportStatus::Failed->value,
                'completed_at' => now(),
                'metadata' => array_merge($export->metadata ?? [], ['error' => $throwable->getMessage()]),
            ])->save();

            $this->logEvent($export, 'failed', $operator, ['error' => $throwable->getMessage()]);
            $this->publishFailureEvent($export);

            throw $throwable;
        }
    }

    private function resolveDefinition(?int $requestedBy = null): ExecutiveReportDefinition
    {
        return ExecutiveReportDefinition::query()->firstOrCreate(
            ['slug' => config('executive_reporting.default_report_slug', 'executive-overview')],
            [
                'name' => 'Executive reporting hub',
                'description' => 'Relatorio executivo central com KPI, recortes e historico de exportacao.',
                'default_filters' => ['days' => 30],
                'visible_sections' => config('executive_reporting.visible_sections', ['summary']),
                'supported_formats' => config('executive_reporting.supported_formats', ['excel', 'pdf']),
                'status' => 'active',
                'created_by' => $requestedBy,
            ]
        );
    }

    private function scopeSummary(ExecutiveAnalyticsSnapshot $executiveAnalyticsSnapshot): string
    {
        $filters = $executiveAnalyticsSnapshot->filter_payload ?? [];

        return sprintf(
            'Periodo %s a %s | plano=%s | canal=%s | carteira=%s | recovery=%s',
            $executiveAnalyticsSnapshot->period_start?->toDateString(),
            $executiveAnalyticsSnapshot->period_end?->toDateString(),
            $filters['plan'] ?? 'all',
            $filters['channel'] ?? 'all',
            $filters['portfolio'] ?? 'all',
            $filters['recovery_status'] ?? 'all',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function logEvent(
        ExecutiveReportExport $executiveReportExport,
        string $eventType,
        ?UsuarioPlataforma $operator = null,
        array $payload = [],
    ): ExecutiveReportExecutionLog {
        return ExecutiveReportExecutionLog::query()->create([
            'executive_report_export_id' => $executiveReportExport->id,
            'event_type' => $eventType,
            'operator_name' => $operator?->name,
            'operator_id' => $operator?->id,
            'event_payload' => array_merge([
                'format' => $executiveReportExport->format->value,
                'status' => $executiveReportExport->export_status->value,
            ], $payload),
            'logged_at' => now(),
        ]);
    }

    private function publishCompletionEvent(ExecutiveReportExport $executiveReportExport, string $completionEvent): void
    {
        $eventType = $completionEvent === 'reexecuted'
            ? 'RELATORIO_EXECUTIVO_REEXECUTADO'
            : 'RELATORIO_EXECUTIVO_GERADO';

        $this->executiveReportingEventPublisher->publish(
            $eventType,
            $executiveReportExport,
            [
                'report_slug' => $executiveReportExport->definition->slug,
                'snapshot_id' => $executiveReportExport->executive_analytics_snapshot_id,
                'export_id' => $executiveReportExport->id,
                'format' => $executiveReportExport->format->value,
                'period_start' => $executiveReportExport->snapshot->period_start?->toDateString(),
                'period_end' => $executiveReportExport->snapshot->period_end?->toDateString(),
                'filters' => $executiveReportExport->snapshot->filter_payload ?? [],
                'operator' => $executiveReportExport->requested_by,
                'status' => $executiveReportExport->export_status->value,
                'occurred_at' => $executiveReportExport->completed_at?->toIso8601String(),
            ],
            ['backbone', 'observability', 'analytics'],
        );
    }

    private function publishFailureEvent(ExecutiveReportExport $executiveReportExport): void
    {
        $this->executiveReportingEventPublisher->publish(
            'RELATORIO_EXECUTIVO_FALHOU',
            $executiveReportExport,
            [
                'report_slug' => $executiveReportExport->definition->slug,
                'snapshot_id' => $executiveReportExport->executive_analytics_snapshot_id,
                'export_id' => $executiveReportExport->id,
                'format' => $executiveReportExport->format->value,
                'filters' => $executiveReportExport->snapshot->filter_payload ?? [],
                'operator' => $executiveReportExport->requested_by,
                'status' => $executiveReportExport->export_status->value,
                'occurred_at' => $executiveReportExport->completed_at?->toIso8601String(),
            ],
            ['backbone', 'observability'],
        );
    }
}
