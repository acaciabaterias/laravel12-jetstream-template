<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\LoadTestBaseline;

class LoadTestBaselineComparator
{
    /**
     * @param  array{throughput_per_minute:int,p95_latency_ms:int,error_rate:float}  $candidate
     * @return array{
     *     status:string,
     *     within_tolerance:bool,
     *     regressed_metrics:array<int, string>,
     *     checks:array<string, array{baseline:int|float,current:int|float,threshold:int|float,direction:string,regressed:bool}>
     * }
     */
    public function compare(LoadTestBaseline $baseline, array $candidate): array
    {
        $throughputThreshold = (int) round(
            $baseline->throughput_per_minute * (1 - (float) config('production_observability.load_baseline.throughput_regression_ratio', 0.15))
        );
        $latencyThreshold = (int) round(
            $baseline->p95_latency_ms * (1 + (float) config('production_observability.load_baseline.latency_regression_ratio', 0.20))
        );
        $errorRateThreshold = round(
            (float) $baseline->error_rate + (float) config('production_observability.load_baseline.error_rate_regression_delta', 0.02),
            4
        );

        $checks = [
            'throughput_per_minute' => [
                'baseline' => $baseline->throughput_per_minute,
                'current' => $candidate['throughput_per_minute'],
                'threshold' => $throughputThreshold,
                'direction' => 'min',
                'regressed' => $candidate['throughput_per_minute'] < $throughputThreshold,
            ],
            'p95_latency_ms' => [
                'baseline' => $baseline->p95_latency_ms,
                'current' => $candidate['p95_latency_ms'],
                'threshold' => $latencyThreshold,
                'direction' => 'max',
                'regressed' => $candidate['p95_latency_ms'] > $latencyThreshold,
            ],
            'error_rate' => [
                'baseline' => round((float) $baseline->error_rate, 4),
                'current' => round($candidate['error_rate'], 4),
                'threshold' => $errorRateThreshold,
                'direction' => 'max',
                'regressed' => round($candidate['error_rate'], 4) > $errorRateThreshold,
            ],
        ];

        $regressedMetrics = collect($checks)
            ->filter(fn (array $check): bool => $check['regressed'])
            ->keys()
            ->values()
            ->all();

        return [
            'status' => $regressedMetrics === [] ? 'within_tolerance' : 'regressed',
            'within_tolerance' => $regressedMetrics === [],
            'regressed_metrics' => $regressedMetrics,
            'checks' => $checks,
        ];
    }
}
