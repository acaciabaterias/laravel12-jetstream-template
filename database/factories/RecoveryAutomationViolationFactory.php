<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RecoveryAutomationPolicyVersion;
use App\Models\RecoveryAutomationViolation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecoveryAutomationViolation>
 */
class RecoveryAutomationViolationFactory extends Factory
{
    protected $model = RecoveryAutomationViolation::class;

    public function definition(): array
    {
        return [
            'recovery_automation_policy_version_id' => RecoveryAutomationPolicyVersion::factory(),
            'recovery_automation_journey_id' => null,
            'recovery_automation_dispatch_id' => null,
            'violation_type' => 'frequency_limit',
            'severity' => 'medium',
            'detected_at' => now(),
            'resolution_status' => 'open',
            'summary' => fake()->sentence(),
            'evidence_payload' => ['source' => 'test'],
        ];
    }
}
