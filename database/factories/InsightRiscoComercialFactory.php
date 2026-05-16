<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\InsightRiscoComercial;
use App\Models\SnapshotAnalyticsComercial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InsightRiscoComercial>
 */
class InsightRiscoComercialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'snapshot_analytics_comercial_id' => SnapshotAnalyticsComercial::factory(),
            'risk_type' => fake()->randomElement(['churn', 'delinquency', 'recovery_stall', 'payment_failure']),
            'severity' => fake()->randomElement(['low', 'medium', 'high']),
            'total_accounts' => fake()->numberBetween(1, 20),
            'total_exposure' => fake()->randomFloat(2, 0, 9000),
            'description' => fake()->sentence(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
