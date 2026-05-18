<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RecoveryAutomationExperiment;
use App\Models\RecoveryAutomationPolicyVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecoveryAutomationExperiment>
 */
class RecoveryAutomationExperimentFactory extends Factory
{
    protected $model = RecoveryAutomationExperiment::class;

    public function definition(): array
    {
        return [
            'recovery_automation_policy_version_id' => RecoveryAutomationPolicyVersion::factory(),
            'name' => fake()->sentence(2),
            'status' => 'draft',
            'allocation_rules' => ['segment' => 'default'],
            'control_ratio' => 0.10,
            'variant_definitions' => ['variant_a' => ['channel' => 'whatsapp'], 'control' => ['holdout' => true]],
            'created_by' => null,
            'metadata' => ['source' => 'test'],
        ];
    }
}
