<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MetricaPerformanceCanal;
use App\Models\SnapshotAnalyticsComercial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MetricaPerformanceCanal>
 */
class MetricaPerformanceCanalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'snapshot_analytics_comercial_id' => SnapshotAnalyticsComercial::factory(),
            'channel_type' => fake()->randomElement(['billing', 'recovery']),
            'channel_name' => fake()->randomElement(['manual', 'pix', 'email', 'whatsapp']),
            'total_cases' => fake()->numberBetween(1, 20),
            'successful_cases' => fake()->numberBetween(0, 20),
            'failed_cases' => fake()->numberBetween(0, 10),
            'recovered_amount' => fake()->randomFloat(2, 0, 5000),
            'conversion_rate' => fake()->randomFloat(4, 0, 1),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
