<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MonitoringTargetCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonitoringTargetCatalog>
 */
class MonitoringTargetCatalogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'flow_name' => fake()->randomElement(['integration_backbone', 'platform_payments', 'platform_recovery', 'platform_analytics', 'production_observability']),
            'target_name' => fake()->unique()->slug(3),
            'environment' => fake()->randomElement(['staging', 'production']),
            'endpoint' => fake()->url(),
            'collector_type' => fake()->randomElement(['prometheus', 'grafana', 'exporter']),
            'status' => fake()->randomElement(['healthy', 'degraded', 'unavailable']),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
