<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SnapshotAnalyticsComercial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SnapshotAnalyticsComercial>
 */
class SnapshotAnalyticsComercialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'snapshot_type' => 'executive',
            'reference_date' => now()->toDateString(),
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->toDateString(),
            'rebuild_status' => 'completed',
            'mrr_amount' => fake()->randomFloat(2, 1000, 10000),
            'churn_count' => fake()->numberBetween(0, 20),
            'churn_rate' => fake()->randomFloat(4, 0, 1),
            'delinquent_count' => fake()->numberBetween(0, 20),
            'recovered_count' => fake()->numberBetween(0, 20),
            'recovered_amount' => fake()->randomFloat(2, 0, 5000),
            'blocked_count' => fake()->numberBetween(0, 10),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
