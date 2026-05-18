<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RecoveryAutomationDispatch;
use App\Models\RecoveryAutomationJourney;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecoveryAutomationDispatch>
 */
class RecoveryAutomationDispatchFactory extends Factory
{
    protected $model = RecoveryAutomationDispatch::class;

    public function definition(): array
    {
        return [
            'recovery_automation_journey_id' => RecoveryAutomationJourney::factory(),
            'acao_recuperacao_receita_id' => null,
            'dispatch_key' => fake()->unique()->uuid(),
            'stage_key' => 'd1',
            'channel' => 'whatsapp',
            'template_key' => 'template-recovery-d1',
            'attempt_number' => 1,
            'dispatch_status' => 'scheduled',
            'scheduled_for' => now()->addMinutes(15),
            'result_payload' => ['source' => 'test'],
            'metadata' => ['source' => 'test'],
        ];
    }
}
