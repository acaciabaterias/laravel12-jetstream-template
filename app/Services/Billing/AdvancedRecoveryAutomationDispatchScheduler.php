<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CasoRecuperacaoReceita;
use App\Models\RecoveryAutomationDispatch;
use App\Support\Billing\RecoveryAutomationDispatchStatus;
use App\Support\Billing\RecoveryAutomationJourneyStatus;
use Illuminate\Support\Arr;

class AdvancedRecoveryAutomationDispatchScheduler
{
    public function __construct(
        private readonly AdvancedRecoveryAutomationJourneyService $journeyService,
        private readonly AdvancedRecoveryAutomationDispatchRules $dispatchRules,
        private readonly RevenueRecoveryActionScheduler $actionScheduler,
        private readonly AdvancedRevenueRecoveryAutomationEventPublisher $eventPublisher,
    ) {}

    public function schedule(CasoRecuperacaoReceita $recoveryCase): ?RecoveryAutomationDispatch
    {
        $case = $recoveryCase->loadMissing('fatura.cliente', 'politica', 'compromissos');
        $evaluation = $this->journeyService->evaluateNextAction($case);

        if ($evaluation === null) {
            return null;
        }

        $journey = $evaluation['journey'];

        if (isset($evaluation['blocked_reason'])) {
            $lastDispatchId = Arr::get($journey->metadata, 'last_dispatch_id');

            if ($evaluation['blocked_reason'] === 'cooldown_active' && $lastDispatchId !== null) {
                return RecoveryAutomationDispatch::query()->find($lastDispatchId);
            }

            return null;
        }

        $existingDispatch = $journey->dispatches()
            ->where('dispatch_key', $evaluation['dispatch_key'])
            ->first();

        if ($existingDispatch !== null) {
            return $existingDispatch;
        }

        $stageDefinition = $evaluation['stage_definition'];
        $channel = $evaluation['channel'];
        $action = $this->actionScheduler->schedule($case, [
            'name' => $stageDefinition['name'],
            'channel' => $channel,
            'delay_hours' => $stageDefinition['delay_hours'],
        ], [
            'automation_policy_version_id' => $evaluation['policy_version']->id,
            'automation_journey_id' => $journey->id,
            'template_key' => $evaluation['template_key'],
            'fallback_reason' => $evaluation['fallback_reason'],
        ]);

        $dispatch = $journey->dispatches()->create([
            'acao_recuperacao_receita_id' => $action->id,
            'dispatch_key' => $evaluation['dispatch_key'],
            'stage_key' => $stageDefinition['name'],
            'channel' => $channel,
            'template_key' => $evaluation['template_key'],
            'attempt_number' => 1,
            'dispatch_status' => RecoveryAutomationDispatchStatus::Scheduled->value,
            'fallback_reason' => $evaluation['fallback_reason'],
            'scheduled_for' => $action->scheduled_for,
            'result_payload' => [
                'action_id' => $action->id,
                'case_id' => $case->id,
            ],
            'metadata' => [
                'source' => 'advanced_recovery_dispatch_scheduler',
            ],
        ]);

        $journey->update([
            'journey_status' => RecoveryAutomationJourneyStatus::Active->value,
            'current_stage' => $stageDefinition['name'],
            'current_channel' => $channel,
            'last_dispatched_at' => now(),
            'next_evaluation_at' => now()->addHours(
                (int) Arr::get(
                    $evaluation['policy_version']->guardrail_rules,
                    'cooldown_hours',
                    config('advanced_revenue_recovery_automation.guardrails.cooldown_hours', 24),
                ),
            ),
            'metadata' => array_merge((array) $journey->metadata, [
                'last_dispatch_id' => $dispatch->id,
            ]),
        ]);

        if ($case->fatura !== null) {
            $this->eventPublisher->publish(
                eventType: 'AUTOMACAO_RECUPERACAO_DISPATCH_AGENDADO',
                recoveryCase: $case,
                payload: [
                    'case_id' => $case->id,
                    'journey_id' => $journey->id,
                    'dispatch_id' => $dispatch->id,
                    'policy_version_id' => $evaluation['policy_version']->id,
                    'channel' => $channel,
                    'stage_key' => $stageDefinition['name'],
                ],
                consumers: config('advanced_revenue_recovery_automation.events.default_consumers', ['platform', 'recovery', 'analytics']),
                schemaDefinition: [
                    'case_id' => 'integer',
                    'journey_id' => 'integer',
                    'dispatch_id' => 'integer',
                    'policy_version_id' => 'integer',
                    'channel' => 'string',
                    'stage_key' => 'string',
                ],
            );
        }

        return $dispatch->refresh();
    }
}
