<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RecorteCoorteComercial;
use App\Models\SnapshotAnalyticsComercial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecorteCoorteComercial>
 */
class RecorteCoorteComercialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'snapshot_analytics_comercial_id' => SnapshotAnalyticsComercial::factory(),
            'cohort_label' => now()->format('Y-m'),
            'cohort_start_date' => now()->startOfMonth()->toDateString(),
            'cohort_end_date' => now()->endOfMonth()->toDateString(),
            'active_subscriptions' => fake()->numberBetween(1, 20),
            'cancelled_subscriptions' => fake()->numberBetween(0, 10),
            'recovered_subscriptions' => fake()->numberBetween(0, 8),
            'delinquent_subscriptions' => fake()->numberBetween(0, 8),
            'mrr_amount' => fake()->randomFloat(2, 500, 5000),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
