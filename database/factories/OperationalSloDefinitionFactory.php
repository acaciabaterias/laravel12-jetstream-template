<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OperationalSloDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationalSloDefinition>
 */
class OperationalSloDefinitionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'flow_name' => fake()->randomElement(['integration_backbone', 'platform_payments', 'platform_recovery', 'platform_analytics']),
            'metric_key' => fake()->randomElement(['backlog_count', 'latency_ms', 'failure_rate', 'open_replays']),
            'target_value' => 0,
            'warning_threshold' => 5,
            'critical_threshold' => 20,
            'severity_mapping' => ['warning' => 'warning', 'critical' => 'critical'],
            'metadata' => ['source' => 'factory'],
        ];
    }
}
