<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MonitoringReadinessEvidence;
use App\Models\UsuarioPlataforma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonitoringReadinessEvidence>
 */
class MonitoringReadinessEvidenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'environment' => fake()->randomElement(['staging', 'production']),
            'evidence_type' => fake()->randomElement(['provisioning', 'rollback', 'validation']),
            'operator_user_id' => UsuarioPlataforma::factory()->billing(),
            'recorded_at' => now(),
            'result_status' => fake()->randomElement(['pending', 'success', 'partial', 'failed']),
            'payload' => ['checks' => ['dashboard-rendered']],
            'notes' => fake()->sentence(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
