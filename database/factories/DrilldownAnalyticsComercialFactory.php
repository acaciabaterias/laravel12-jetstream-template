<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DrilldownAnalyticsComercial;
use App\Models\SnapshotAnalyticsComercial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DrilldownAnalyticsComercial>
 */
class DrilldownAnalyticsComercialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'snapshot_analytics_comercial_id' => SnapshotAnalyticsComercial::factory(),
            'source_type' => fake()->randomElement(['subscription', 'invoice', 'recovery_case']),
            'source_id' => fake()->numberBetween(1, 999),
            'dimension_type' => fake()->randomElement(['status', 'channel', 'cohort']),
            'dimension_value' => fake()->word(),
            'metric_key' => fake()->randomElement(['mrr', 'churn', 'delinquency', 'recovery']),
            'metric_value' => fake()->randomFloat(2, 0, 5000),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
