<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MonitoringProbeSnapshot;
use App\Models\MonitoringTargetCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonitoringProbeSnapshot>
 */
class MonitoringProbeSnapshotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'monitoring_target_catalog_id' => MonitoringTargetCatalog::factory(),
            'reference_at' => now(),
            'scrape_status' => fake()->randomElement(['healthy', 'degraded', 'unavailable']),
            'latency_ms' => fake()->numberBetween(100, 5000),
            'sample_count' => fake()->numberBetween(0, 500),
            'failure_reason' => null,
            'metadata' => ['source' => 'factory'],
        ];
    }
}
