<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\LoadScenarioProfile;

class CriticalLoadBenchmarkComparator
{
    /**
     * @param  array{
     *     throughput_per_minute:int,
     *     p95_latency_ms:int,
     *     error_rate:float
     * }  $metrics
     * @return array{
     *     status:string,
     *     within_tolerance:bool,
     *     regressed_metrics:array<int, string>
     * }
     */
    public function compare(LoadScenarioProfile $scenario, array $metrics): array
    {
        $throughputRegressionRatio = (float) config('load_optimization.tolerances.throughput_regression_ratio', 0.90);
        $latencyRegressionRatio = (float) config('load_optimization.tolerances.latency_regression_ratio', 1.15);
        $errorRateRegressionDelta = (float) config('load_optimization.tolerances.error_rate_regression_delta', 0.01);
        $regressedMetrics = [];

        if ((int) $metrics['throughput_per_minute'] < (int) round($scenario->expected_throughput_per_minute * $throughputRegressionRatio)) {
            $regressedMetrics[] = 'throughput_per_minute';
        }

        if ((int) $metrics['p95_latency_ms'] > (int) round($scenario->expected_p95_latency_ms * $latencyRegressionRatio)) {
            $regressedMetrics[] = 'p95_latency_ms';
        }

        if ((float) $metrics['error_rate'] > ((float) $scenario->expected_error_rate + $errorRateRegressionDelta)) {
            $regressedMetrics[] = 'error_rate';
        }

        if ($regressedMetrics !== []) {
            return [
                'status' => 'regressed',
                'within_tolerance' => false,
                'regressed_metrics' => $regressedMetrics,
            ];
        }

        if (
            (int) $metrics['throughput_per_minute'] > $scenario->expected_throughput_per_minute
            && (int) $metrics['p95_latency_ms'] <= $scenario->expected_p95_latency_ms
            && (float) $metrics['error_rate'] <= (float) $scenario->expected_error_rate
        ) {
            return [
                'status' => 'improved',
                'within_tolerance' => true,
                'regressed_metrics' => [],
            ];
        }

        return [
            'status' => 'stable',
            'within_tolerance' => true,
            'regressed_metrics' => [],
        ];
    }
}
