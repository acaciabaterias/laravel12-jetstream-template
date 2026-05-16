<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\BenchmarkExecutionRecord;
use App\Models\LoadScenarioProfile;
use App\Models\PerformanceBottleneckRecord;
use App\Models\PerformanceRollbackEvidence;
use App\Models\TuningChangeRecord;

class CriticalLoadInspectionService
{
    public function __construct(
        private readonly CriticalLoadBenchmarkService $criticalLoadBenchmarkService,
    ) {}

    /**
     * @param  array{
     *     flow_name?:string|null,
     *     comparison_status?:string|null,
     *     category?:string|null,
     *     environment?:string|null,
     *     tuning_status?:string|null,
     *     limit?:int|null
     * }  $filters
     * @return array{
     *     summary: array<string, int>,
     *     scenarios: array<int, array<string, mixed>>,
     *     executions: array<int, array<string, mixed>>,
     *     bottlenecks: array<int, array<string, mixed>>,
     *     tuning_changes: array<int, array<string, mixed>>,
     *     rollback_evidences: array<int, array<string, mixed>>
     * }
     */
    public function inspect(array $filters = []): array
    {
        $flowName = (string) ($filters['flow_name'] ?? '');
        $environment = (string) ($filters['environment'] ?? '');
        $executions = $this->criticalLoadBenchmarkService->executions([
            'flow_name' => $flowName,
            'comparison_status' => (string) ($filters['comparison_status'] ?? ''),
        ], (int) ($filters['limit'] ?? 25));
        $scenarios = $this->criticalLoadBenchmarkService->latestScenarios($flowName !== '' ? $flowName : null, (int) ($filters['limit'] ?? 25))
            ->when($environment !== '', fn ($collection) => $collection->where('environment', $environment)->values());
        $bottlenecks = PerformanceBottleneckRecord::query()
            ->with('benchmarkExecution.scenario')
            ->when($flowName !== '', fn ($query) => $query->where('flow_name', $flowName))
            ->when(($filters['category'] ?? '') !== '', fn ($query) => $query->where('category', $filters['category']))
            ->latest('updated_at')
            ->limit((int) ($filters['limit'] ?? 25))
            ->get();
        $tuningChanges = TuningChangeRecord::query()
            ->with(['baselineExecution.scenario', 'validationExecution.scenario'])
            ->when($flowName !== '', fn ($query) => $query->where('flow_name', $flowName))
            ->when($environment !== '', fn ($query) => $query->where('environment', $environment))
            ->when(($filters['tuning_status'] ?? '') !== '', fn ($query) => $query->where('status', $filters['tuning_status']))
            ->latest('updated_at')
            ->limit((int) ($filters['limit'] ?? 25))
            ->get();
        $rollbackEvidences = PerformanceRollbackEvidence::query()
            ->with(['tuningChange', 'operator'])
            ->when($flowName !== '', fn ($query) => $query->whereHas('tuningChange', fn ($tuningQuery) => $tuningQuery->where('flow_name', $flowName)))
            ->latest('recorded_at')
            ->limit((int) ($filters['limit'] ?? 25))
            ->get();

        return [
            'summary' => $this->criticalLoadBenchmarkService->summarize(),
            'scenarios' => $scenarios->map(fn (LoadScenarioProfile $scenario): array => [
                'id' => $scenario->id,
                'flow_name' => $scenario->flow_name,
                'scenario_name' => $scenario->scenario_name,
                'environment' => $scenario->environment,
                'request_budget' => $scenario->request_budget,
                'duration_seconds' => $scenario->duration_seconds,
                'concurrency_level' => $scenario->concurrency_level,
                'expected_throughput_per_minute' => $scenario->expected_throughput_per_minute,
                'expected_p95_latency_ms' => $scenario->expected_p95_latency_ms,
                'expected_error_rate' => (float) $scenario->expected_error_rate,
            ])->values()->all(),
            'executions' => $executions->getCollection()->map(fn (BenchmarkExecutionRecord $execution): array => [
                'id' => $execution->id,
                'scenario_name' => $execution->scenario?->scenario_name,
                'flow_name' => $execution->scenario?->flow_name,
                'environment' => $execution->scenario?->environment,
                'throughput_per_minute' => $execution->throughput_per_minute,
                'p95_latency_ms' => $execution->p95_latency_ms,
                'error_rate' => (float) $execution->error_rate,
                'status' => $execution->status->value,
                'comparison_status' => $execution->comparison_status->value,
                'started_at' => $execution->started_at?->toAtomString(),
                'finished_at' => $execution->finished_at?->toAtomString(),
            ])->values()->all(),
            'bottlenecks' => $bottlenecks->map(fn (PerformanceBottleneckRecord $bottleneck): array => [
                'id' => $bottleneck->id,
                'flow_name' => $bottleneck->flow_name,
                'category' => $bottleneck->category->value,
                'component_name' => $bottleneck->component_name,
                'summary' => $bottleneck->summary,
                'impact_level' => $bottleneck->impact_level->value,
                'benchmark_execution_id' => $bottleneck->benchmarkExecution?->id,
            ])->values()->all(),
            'tuning_changes' => $tuningChanges->map(fn (TuningChangeRecord $change): array => [
                'id' => $change->id,
                'flow_name' => $change->flow_name,
                'environment' => $change->environment,
                'change_key' => $change->change_key,
                'change_type' => $change->change_type,
                'status' => $change->status->value,
                'rollback_recommended' => $change->rollback_recommended,
                'baseline_execution_id' => $change->baseline_execution_id,
                'validation_execution_id' => $change->validation_execution_id,
            ])->values()->all(),
            'rollback_evidences' => $rollbackEvidences->map(fn (PerformanceRollbackEvidence $evidence): array => [
                'id' => $evidence->id,
                'change_key' => $evidence->tuningChange?->change_key,
                'flow_name' => $evidence->tuningChange?->flow_name,
                'result_status' => $evidence->result_status->value,
                'rollback_reason' => $evidence->rollback_reason,
                'operator' => $evidence->operator?->name,
                'recorded_at' => $evidence->recorded_at?->toAtomString(),
            ])->values()->all(),
        ];
    }
}
