<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BenchmarkExecutionRecord;
use App\Models\PerformanceBottleneckRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformanceBottleneckRecord>
 */
class PerformanceBottleneckRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'benchmark_execution_record_id' => BenchmarkExecutionRecord::factory(),
            'flow_name' => fake()->randomElement(['integration_backbone', 'platform_payments']),
            'category' => fake()->randomElement(['database', 'queue', 'external_endpoint', 'application']),
            'component_name' => fake()->slug(2),
            'summary' => fake()->sentence(),
            'impact_level' => fake()->randomElement(['warning', 'critical']),
            'evidence_payload' => ['samples' => [1, 2, 3]],
            'metadata' => ['source' => 'factory'],
        ];
    }
}
