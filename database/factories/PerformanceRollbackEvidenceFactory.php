<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PerformanceRollbackEvidence;
use App\Models\TuningChangeRecord;
use App\Models\UsuarioPlataforma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformanceRollbackEvidence>
 */
class PerformanceRollbackEvidenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tuning_change_record_id' => TuningChangeRecord::factory(),
            'operator_user_id' => UsuarioPlataforma::factory()->billing(),
            'recorded_at' => now(),
            'result_status' => fake()->randomElement(['pending', 'success', 'partial', 'failed']),
            'rollback_reason' => fake()->sentence(),
            'payload' => ['restored_baseline' => true],
            'metadata' => ['source' => 'factory'],
        ];
    }
}
