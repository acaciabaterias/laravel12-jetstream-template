<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OperationalIncidentRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationalIncidentRecord>
 */
class OperationalIncidentRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'incident_key' => fake()->unique()->slug(3),
            'flow_name' => fake()->randomElement(['integration_backbone', 'platform_payments', 'platform_recovery', 'platform_analytics']),
            'severity' => fake()->randomElement(['warning', 'critical']),
            'status' => fake()->randomElement(['open', 'acknowledged']),
            'opened_at' => now()->subHour(),
            'acknowledged_at' => null,
            'resolved_at' => null,
            'summary' => fake()->sentence(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
