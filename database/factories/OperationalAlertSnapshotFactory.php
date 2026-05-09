<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OperationalAlertSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationalAlertSnapshot>
 */
class OperationalAlertSnapshotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference_at' => now(),
            'flow_name' => fake()->randomElement(['integration_backbone', 'platform_payments', 'platform_recovery', 'platform_analytics']),
            'status' => fake()->randomElement(['healthy', 'degraded', 'unavailable']),
            'severity' => fake()->randomElement(['healthy', 'warning', 'critical']),
            'backlog_count' => fake()->numberBetween(0, 50),
            'latency_ms' => fake()->numberBetween(100, 5000),
            'failure_rate' => fake()->randomFloat(4, 0, 0.5),
            'open_replays' => fake()->numberBetween(0, 10),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
