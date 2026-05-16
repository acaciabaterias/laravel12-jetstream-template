<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\LoadScenarioProfile;
use App\Services\Operations\CriticalLoadBenchmarkComparator;
use Tests\NonDatabaseTestCase;

class CriticalLoadComparisonRulesTest extends NonDatabaseTestCase
{
    public function test_it_classifies_improved_stable_and_regressed_benchmarks(): void
    {
        $comparator = app(CriticalLoadBenchmarkComparator::class);
        $scenario = LoadScenarioProfile::factory()->make([
            'expected_throughput_per_minute' => 500,
            'expected_p95_latency_ms' => 1000,
            'expected_error_rate' => 0.01,
        ]);

        $improved = $comparator->compare($scenario, [
            'throughput_per_minute' => 560,
            'p95_latency_ms' => 900,
            'error_rate' => 0.009,
        ]);
        $stable = $comparator->compare($scenario, [
            'throughput_per_minute' => 480,
            'p95_latency_ms' => 1100,
            'error_rate' => 0.015,
        ]);
        $regressed = $comparator->compare($scenario, [
            'throughput_per_minute' => 430,
            'p95_latency_ms' => 1300,
            'error_rate' => 0.03,
        ]);

        $this->assertSame('improved', $improved['status']);
        $this->assertSame('stable', $stable['status']);
        $this->assertSame('regressed', $regressed['status']);
        $this->assertSame([
            'throughput_per_minute',
            'p95_latency_ms',
            'error_rate',
        ], $regressed['regressed_metrics']);
    }
}
