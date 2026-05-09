<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LoadTestBaseline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoadTestBaseline>
 */
class LoadTestBaselineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'scenario_name' => fake()->words(2, true),
            'flow_name' => fake()->randomElement(['integration_backbone', 'platform_payments', 'platform_recovery', 'platform_analytics']),
            'throughput_per_minute' => fake()->numberBetween(50, 500),
            'p95_latency_ms' => fake()->numberBetween(300, 3000),
            'error_rate' => fake()->randomFloat(4, 0, 0.2),
            'environment_notes' => fake()->sentence(),
            'accepted_at' => now(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
