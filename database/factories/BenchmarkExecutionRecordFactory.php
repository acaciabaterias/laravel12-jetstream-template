<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BenchmarkExecutionRecord;
use App\Models\LoadScenarioProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BenchmarkExecutionRecord>
 */
class BenchmarkExecutionRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'load_scenario_profile_id' => LoadScenarioProfile::factory(),
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'throughput_per_minute' => fake()->numberBetween(120, 900),
            'p95_latency_ms' => fake()->numberBetween(250, 1800),
            'error_rate' => fake()->randomFloat(4, 0, 0.05),
            'status' => fake()->randomElement(['pending', 'completed', 'incomplete']),
            'comparison_status' => fake()->randomElement(['baseline', 'improved', 'stable', 'regressed']),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
