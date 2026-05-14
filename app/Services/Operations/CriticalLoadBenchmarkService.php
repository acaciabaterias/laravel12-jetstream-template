<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\BenchmarkExecutionRecord;
use App\Models\LoadScenarioProfile;
use App\Support\Operations\BenchmarkComparisonStatus;
use App\Support\Operations\BenchmarkExecutionStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CriticalLoadBenchmarkService
{
    public function __construct(
        private readonly CriticalLoadBenchmarkComparator $criticalLoadBenchmarkComparator,
        private readonly CriticalLoadEventPublisher $criticalLoadEventPublisher,
    ) {}

    /**
     * @param  array{
     *     flow_name:string,
     *     scenario_name:string,
     *     environment:string,
     *     request_budget:int,
     *     duration_seconds:int,
     *     concurrency_level:int,
     *     expected_throughput_per_minute:int,
     *     expected_p95_latency_ms:int,
     *     expected_error_rate:float,
     *     metadata?:array<string, mixed>
     * }  $attributes
     */
    public function registerScenario(array $attributes): LoadScenarioProfile
    {
        return LoadScenarioProfile::query()->create([
            'flow_name' => $attributes['flow_name'],
            'scenario_name' => $attributes['scenario_name'],
            'environment' => $attributes['environment'],
            'request_budget' => $attributes['request_budget'],
            'duration_seconds' => $attributes['duration_seconds'],
            'concurrency_level' => $attributes['concurrency_level'],
            'expected_throughput_per_minute' => $attributes['expected_throughput_per_minute'],
            'expected_p95_latency_ms' => $attributes['expected_p95_latency_ms'],
            'expected_error_rate' => $attributes['expected_error_rate'],
            'metadata' => $attributes['metadata'] ?? [],
        ]);
    }

    /**
     * @param  array{
     *     started_at?:Carbon|string|null,
     *     finished_at?:Carbon|string|null,
     *     throughput_per_minute:int,
     *     p95_latency_ms:int,
     *     error_rate:float,
     *     metadata?:array<string, mixed>
     * }  $attributes
     */
    public function recordExecution(LoadScenarioProfile $scenario, array $attributes): BenchmarkExecutionRecord
    {
        $comparison = $this->criticalLoadBenchmarkComparator->compare($scenario, [
            'throughput_per_minute' => $attributes['throughput_per_minute'],
            'p95_latency_ms' => $attributes['p95_latency_ms'],
            'error_rate' => $attributes['error_rate'],
        ]);

        $record = BenchmarkExecutionRecord::query()->create([
            'load_scenario_profile_id' => $scenario->id,
            'started_at' => $attributes['started_at'] ?? now()->subMinute(),
            'finished_at' => $attributes['finished_at'] ?? now(),
            'throughput_per_minute' => $attributes['throughput_per_minute'],
            'p95_latency_ms' => $attributes['p95_latency_ms'],
            'error_rate' => $attributes['error_rate'],
            'status' => BenchmarkExecutionStatus::Completed,
            'comparison_status' => match ($comparison['status']) {
                'improved' => BenchmarkComparisonStatus::Improved,
                'regressed' => BenchmarkComparisonStatus::Regressed,
                default => BenchmarkComparisonStatus::Stable,
            },
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'regressed_metrics' => $comparison['regressed_metrics'],
            ]),
        ]);

        if ($record->comparison_status === BenchmarkComparisonStatus::Regressed) {
            $this->criticalLoadEventPublisher->publish(
                'BENCHMARK_REGRESSIVO_DETECTADO',
                sprintf('%s:%s:%s', $scenario->flow_name, $scenario->scenario_name, $record->id),
                [
                    'flow_name' => $scenario->flow_name,
                    'scenario_name' => $scenario->scenario_name,
                    'environment' => $scenario->environment,
                    'throughput_per_minute' => $record->throughput_per_minute,
                    'p95_latency_ms' => $record->p95_latency_ms,
                    'error_rate' => $record->error_rate,
                    'regressed_metrics' => $comparison['regressed_metrics'],
                ],
                ['platform', 'support'],
                [
                    'type' => 'benchmark-regression',
                ],
            );
        }

        return $record->fresh();
    }

    public function promoteBaseline(LoadScenarioProfile $scenario, BenchmarkExecutionRecord $record): LoadScenarioProfile
    {
        $scenario->forceFill([
            'expected_throughput_per_minute' => $record->throughput_per_minute,
            'expected_p95_latency_ms' => $record->p95_latency_ms,
            'expected_error_rate' => $record->error_rate,
            'metadata' => array_merge($scenario->metadata ?? [], [
                'baseline_execution_id' => $record->id,
                'baseline_promoted_at' => now()->toAtomString(),
            ]),
        ])->save();

        $this->criticalLoadEventPublisher->publish(
            'BASELINE_CARGA_PROMOVIDA',
            sprintf('%s:%s:%s', $scenario->flow_name, $scenario->scenario_name, $record->id),
            [
                'flow_name' => $scenario->flow_name,
                'scenario_name' => $scenario->scenario_name,
                'environment' => $scenario->environment,
                'baseline_execution_id' => $record->id,
            ],
            ['platform'],
            [
                'type' => 'benchmark-baseline-promotion',
            ],
        );

        return $scenario->fresh();
    }

    /**
     * @param  array{flow_name?:string, comparison_status?:string}  $filters
     */
    public function executions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return BenchmarkExecutionRecord::query()
            ->with('scenario')
            ->when(($filters['flow_name'] ?? '') !== '', fn ($query) => $query->whereHas('scenario', fn ($scenarioQuery) => $scenarioQuery->where('flow_name', $filters['flow_name'])))
            ->when(($filters['comparison_status'] ?? '') !== '', fn ($query) => $query->where('comparison_status', $filters['comparison_status']))
            ->latest('started_at')
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, LoadScenarioProfile>
     */
    public function latestScenarios(?string $flowName = null, int $limit = 12): Collection
    {
        return LoadScenarioProfile::query()
            ->when($flowName !== null && $flowName !== '', fn ($query) => $query->where('flow_name', $flowName))
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<string, int>
     */
    public function summarize(): array
    {
        return [
            'scenarios' => LoadScenarioProfile::query()->count(),
            'improved' => BenchmarkExecutionRecord::query()->where('comparison_status', BenchmarkComparisonStatus::Improved->value)->count(),
            'stable' => BenchmarkExecutionRecord::query()->where('comparison_status', BenchmarkComparisonStatus::Stable->value)->count(),
            'regressed' => BenchmarkExecutionRecord::query()->where('comparison_status', BenchmarkComparisonStatus::Regressed->value)->count(),
        ];
    }
}
