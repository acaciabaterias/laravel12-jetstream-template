<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\OperationalIncidentRecord;

class ProductionObservabilityInspectionService
{
    public function __construct(
        private readonly ProductionObservabilitySummaryService $productionObservabilitySummaryService,
        private readonly LoadTestBaselineService $loadTestBaselineService,
    ) {}

    /**
     * @param array{
     *     flow_name?:string|null,
     *     severity?:string|null,
     *     status?:string|null,
     *     incident_status?:string|null,
     *     scenario_name?:string|null,
     *     throughput_per_minute?:int|null,
     *     p95_latency_ms?:int|null,
     *     error_rate?:float|null,
     *     limit?:int|null
     * } $filters
     * @return array{
     *     summary: array<string, int>,
     *     snapshots: array<int, array<string, mixed>>,
     *     baselines: array<int, array<string, mixed>>,
     *     incidents: array<int, array<string, mixed>>,
     *     comparison:?array<string, mixed>
     * }
     */
    public function inspect(array $filters = []): array
    {
        $flowName = (string) ($filters['flow_name'] ?? '');
        $snapshots = $this->productionObservabilitySummaryService->snapshots([
            'flow_name' => $flowName,
            'severity' => (string) ($filters['severity'] ?? ''),
            'status' => (string) ($filters['status'] ?? ''),
        ], (int) ($filters['limit'] ?? 25));
        $baselines = $this->loadTestBaselineService->latest($flowName !== '' ? $flowName : null);
        $comparison = null;

        if (
            ($filters['scenario_name'] ?? null) !== null
            && $flowName !== ''
            && ($filters['throughput_per_minute'] ?? null) !== null
            && ($filters['p95_latency_ms'] ?? null) !== null
            && ($filters['error_rate'] ?? null) !== null
        ) {
            $comparison = $this->loadTestBaselineService->compare([
                'scenario_name' => (string) $filters['scenario_name'],
                'flow_name' => $flowName,
                'throughput_per_minute' => (int) $filters['throughput_per_minute'],
                'p95_latency_ms' => (int) $filters['p95_latency_ms'],
                'error_rate' => (float) $filters['error_rate'],
            ]);
        }

        $incidents = OperationalIncidentRecord::query()
            ->with(['evidences.operator'])
            ->when($flowName !== '', fn ($query) => $query->where('flow_name', $flowName))
            ->when(($filters['incident_status'] ?? '') !== '', fn ($query) => $query->where('status', $filters['incident_status']))
            ->latest('opened_at')
            ->limit((int) ($filters['limit'] ?? 25))
            ->get();

        return [
            'summary' => $this->productionObservabilitySummaryService->summarize(),
            'snapshots' => $snapshots->getCollection()->map(fn ($snapshot): array => [
                'id' => $snapshot->id,
                'flow_name' => $snapshot->flow_name,
                'status' => $snapshot->status->value,
                'severity' => $snapshot->severity->value,
                'backlog_count' => $snapshot->backlog_count,
                'latency_ms' => $snapshot->latency_ms,
                'failure_rate' => (float) $snapshot->failure_rate,
                'open_replays' => $snapshot->open_replays,
                'reference_at' => $snapshot->reference_at?->toAtomString(),
            ])->values()->all(),
            'baselines' => $baselines->map(fn ($baseline): array => [
                'id' => $baseline->id,
                'scenario_name' => $baseline->scenario_name,
                'flow_name' => $baseline->flow_name,
                'throughput_per_minute' => $baseline->throughput_per_minute,
                'p95_latency_ms' => $baseline->p95_latency_ms,
                'error_rate' => round((float) $baseline->error_rate, 4),
                'accepted_at' => $baseline->accepted_at?->toAtomString(),
            ])->values()->all(),
            'incidents' => $incidents->map(fn ($incident): array => [
                'id' => $incident->id,
                'incident_key' => $incident->incident_key,
                'flow_name' => $incident->flow_name,
                'severity' => $incident->severity->value,
                'status' => $incident->status->value,
                'summary' => $incident->summary,
                'opened_at' => $incident->opened_at?->toAtomString(),
                'acknowledged_at' => $incident->acknowledged_at?->toAtomString(),
                'resolved_at' => $incident->resolved_at?->toAtomString(),
                'evidences' => $incident->evidences->map(fn ($evidence): array => [
                    'id' => $evidence->id,
                    'execution_type' => $evidence->execution_type,
                    'result_status' => $evidence->result_status->value,
                    'operator' => $evidence->operator?->name,
                    'started_at' => $evidence->started_at?->toAtomString(),
                    'finished_at' => $evidence->finished_at?->toAtomString(),
                ])->values()->all(),
            ])->values()->all(),
            'comparison' => $comparison,
        ];
    }
}
