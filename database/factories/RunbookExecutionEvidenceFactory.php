<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OperationalIncidentRecord;
use App\Models\RunbookExecutionEvidence;
use App\Models\UsuarioPlataforma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RunbookExecutionEvidence>
 */
class RunbookExecutionEvidenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'operational_incident_record_id' => OperationalIncidentRecord::factory(),
            'execution_type' => fake()->randomElement(['replay', 'rollback', 'restore_validation']),
            'operator_user_id' => UsuarioPlataforma::factory()->billing(),
            'started_at' => now()->subMinutes(15),
            'finished_at' => now()->subMinutes(10),
            'result_status' => fake()->randomElement(['success', 'partial', 'failed']),
            'evidence_payload' => ['steps' => ['validated']],
            'notes' => fake()->sentence(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
