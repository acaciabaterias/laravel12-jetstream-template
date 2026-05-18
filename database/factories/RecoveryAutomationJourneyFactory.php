<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CasoRecuperacaoReceita;
use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecoveryAutomationJourney>
 */
class RecoveryAutomationJourneyFactory extends Factory
{
    protected $model = RecoveryAutomationJourney::class;

    public function definition(): array
    {
        return [
            'caso_recuperacao_receita_id' => CasoRecuperacaoReceita::factory(),
            'recovery_automation_policy_version_id' => RecoveryAutomationPolicyVersion::factory(),
            'recovery_automation_experiment_id' => null,
            'variant_key' => 'variant_a',
            'journey_status' => 'active',
            'current_stage' => 'd1',
            'current_channel' => 'whatsapp',
            'next_evaluation_at' => now()->addHour(),
            'metadata' => ['source' => 'test'],
        ];
    }
}
