<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LoadScenarioProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoadScenarioProfile>
 */
class LoadScenarioProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'flow_name' => fake()->randomElement(['integration_backbone', 'platform_payments', 'platform_recovery']),
            'scenario_name' => fake()->unique()->slug(3),
            'environment' => fake()->randomElement(['staging', 'production']),
            'request_budget' => fake()->numberBetween(100, 1000),
            'duration_seconds' => fake()->numberBetween(60, 600),
            'concurrency_level' => fake()->numberBetween(5, 50),
            'expected_throughput_per_minute' => fake()->numberBetween(150, 800),
            'expected_p95_latency_ms' => fake()->numberBetween(300, 1500),
            'expected_error_rate' => fake()->randomFloat(4, 0, 0.03),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
