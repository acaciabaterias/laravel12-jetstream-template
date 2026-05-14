<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BenchmarkExecutionRecord;
use App\Models\TuningChangeRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TuningChangeRecord>
 */
class TuningChangeRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'flow_name' => fake()->randomElement(['integration_backbone', 'platform_payments']),
            'environment' => fake()->randomElement(['staging', 'production']),
            'change_key' => fake()->unique()->slug(3),
            'hypothesis_summary' => fake()->sentence(),
            'change_type' => fake()->randomElement(['index', 'query_rewrite', 'queue_tuning']),
            'applied_at' => now()->subMinutes(10),
            'status' => fake()->randomElement(['pending', 'validated', 'promoted', 'rolled_back']),
            'baseline_execution_id' => BenchmarkExecutionRecord::factory(),
            'validation_execution_id' => BenchmarkExecutionRecord::factory(),
            'rollback_recommended' => false,
            'metadata' => ['source' => 'factory'],
        ];
    }
}
