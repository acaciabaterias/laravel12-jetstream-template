<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\LoadTestBaseline;
use App\Services\Operations\LoadTestBaselineComparator;
use Tests\NonDatabaseTestCase;

class ProductionObservabilityBaselineRulesTest extends NonDatabaseTestCase
{
    public function test_it_classifies_within_tolerance_and_regressed_baseline_checks(): void
    {
        $comparator = app(LoadTestBaselineComparator::class);
        $baseline = LoadTestBaseline::factory()->make([
            'throughput_per_minute' => 500,
            'p95_latency_ms' => 1000,
            'error_rate' => 0.01,
        ]);

        $withinTolerance = $comparator->compare($baseline, [
            'throughput_per_minute' => 460,
            'p95_latency_ms' => 1180,
            'error_rate' => 0.02,
        ]);

        $regressed = $comparator->compare($baseline, [
            'throughput_per_minute' => 390,
            'p95_latency_ms' => 1300,
            'error_rate' => 0.05,
        ]);

        $this->assertSame('within_tolerance', $withinTolerance['status']);
        $this->assertTrue($withinTolerance['within_tolerance']);
        $this->assertSame([], $withinTolerance['regressed_metrics']);

        $this->assertSame('regressed', $regressed['status']);
        $this->assertFalse($regressed['within_tolerance']);
        $this->assertSame([
            'throughput_per_minute',
            'p95_latency_ms',
            'error_rate',
        ], $regressed['regressed_metrics']);
    }
}
