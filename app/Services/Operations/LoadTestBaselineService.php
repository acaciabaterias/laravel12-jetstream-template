<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\LoadTestBaseline;
use Illuminate\Support\Collection;

class LoadTestBaselineService
{
    public function __construct(
        private readonly LoadTestBaselineComparator $loadTestBaselineComparator,
        private readonly ProductionObservabilityEventPublisher $productionObservabilityEventPublisher,
    ) {}

    /**
     * @param array{
     *     scenario_name:string,
     *     flow_name:string,
     *     throughput_per_minute:int,
     *     p95_latency_ms:int,
     *     error_rate:float,
     *     environment_notes:?string,
     *     accepted_at:mixed,
     *     metadata?:array<string, mixed>
     * } $attributes
     */
    public function record(array $attributes): LoadTestBaseline
    {
        $baseline = LoadTestBaseline::query()->create([
            'scenario_name' => $attributes['scenario_name'],
            'flow_name' => $attributes['flow_name'],
            'throughput_per_minute' => $attributes['throughput_per_minute'],
            'p95_latency_ms' => $attributes['p95_latency_ms'],
            'error_rate' => $attributes['error_rate'],
            'environment_notes' => $attributes['environment_notes'],
            'accepted_at' => $attributes['accepted_at'],
            'metadata' => $attributes['metadata'] ?? [],
        ]);

        $this->productionObservabilityEventPublisher->publishBaselineUpdated($baseline);

        return $baseline;
    }

    /**
     * @return Collection<int, LoadTestBaseline>
     */
    public function latest(?string $flowName = null, int $limit = 6): Collection
    {
        return LoadTestBaseline::query()
            ->when($flowName !== null && $flowName !== '', fn ($query) => $query->where('flow_name', $flowName))
            ->orderByDesc('accepted_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @param array{
     *     scenario_name:string,
     *     flow_name:string,
     *     throughput_per_minute:int,
     *     p95_latency_ms:int,
     *     error_rate:float
     * } $candidate
     * @return array{
     *     status:string,
     *     within_tolerance:bool,
     *     baseline:?array<string, mixed>,
     *     candidate:array<string, mixed>,
     *     regressed_metrics:array<int, string>,
     *     checks:array<string, array<string, mixed>>
     * }
     */
    public function compare(array $candidate): array
    {
        $baseline = LoadTestBaseline::query()
            ->where('scenario_name', $candidate['scenario_name'])
            ->where('flow_name', $candidate['flow_name'])
            ->orderByDesc('accepted_at')
            ->orderByDesc('id')
            ->first();

        if ($baseline === null) {
            return [
                'status' => 'missing_baseline',
                'within_tolerance' => false,
                'baseline' => null,
                'candidate' => $candidate,
                'regressed_metrics' => [],
                'checks' => [],
            ];
        }

        $comparison = $this->loadTestBaselineComparator->compare($baseline, [
            'throughput_per_minute' => $candidate['throughput_per_minute'],
            'p95_latency_ms' => $candidate['p95_latency_ms'],
            'error_rate' => $candidate['error_rate'],
        ]);

        return [
            'status' => $comparison['status'],
            'within_tolerance' => $comparison['within_tolerance'],
            'baseline' => [
                'id' => $baseline->id,
                'scenario_name' => $baseline->scenario_name,
                'flow_name' => $baseline->flow_name,
                'throughput_per_minute' => $baseline->throughput_per_minute,
                'p95_latency_ms' => $baseline->p95_latency_ms,
                'error_rate' => round((float) $baseline->error_rate, 4),
                'accepted_at' => $baseline->accepted_at?->toAtomString(),
            ],
            'candidate' => $candidate,
            'regressed_metrics' => $comparison['regressed_metrics'],
            'checks' => $comparison['checks'],
        ];
    }
}
