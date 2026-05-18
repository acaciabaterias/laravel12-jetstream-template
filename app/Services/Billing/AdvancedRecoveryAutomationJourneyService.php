<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CasoRecuperacaoReceita;
use App\Models\CompromissoPagamento;
use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Support\Billing\RecoveryAutomationJourneyStatus;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AdvancedRecoveryAutomationJourneyService
{
    public function __construct(
        private readonly AdvancedRecoveryAutomationPolicyResolver $policyResolver,
        private readonly AdvancedRecoveryAutomationDispatchRules $dispatchRules,
        private readonly AdvancedRecoveryAutomationExperimentService $experimentService,
    ) {}

    /**
     * @return array{
     *     journey: RecoveryAutomationJourney,
     *     policy_version: RecoveryAutomationPolicyVersion,
     *     blocked_reason?: string,
     *     stage_definition?: array{name: string, channel: string, delay_hours: int},
     *     channel?: string,
     *     template_key?: string,
     *     fallback_reason?: string|null,
     *     dispatch_key?: string
     * }|null
     */
    public function evaluateNextAction(CasoRecuperacaoReceita $recoveryCase): ?array
    {
        $case = $recoveryCase->loadMissing('politica', 'fatura', 'compromissos');
        $policyVersion = $this->policyResolver->resolveForCase($case);

        if ($policyVersion === null) {
            return null;
        }

        $journey = $this->startOrRefreshJourney($case, $policyVersion);
        $stageDefinition = $this->resolveStageDefinition($case, $journey);
        $suppressionReason = $this->dispatchRules->resolveSuppressionReason($case, $journey);

        if ($suppressionReason !== null) {
            $journey->update([
                'journey_status' => RecoveryAutomationJourneyStatus::Paused->value,
                'suppressed_until' => $this->resolveSuppressedUntil($case, $journey),
                'next_evaluation_at' => $this->resolveSuppressedUntil($case, $journey),
                'metadata' => array_merge((array) $journey->metadata, [
                    'last_blocked_reason' => $suppressionReason,
                ]),
            ]);

            return [
                'journey' => $journey->refresh(),
                'policy_version' => $policyVersion,
                'blocked_reason' => $suppressionReason,
            ];
        }

        if ($this->dispatchRules->isWithinCooldown($journey, $policyVersion)) {
            $journey->update([
                'journey_status' => RecoveryAutomationJourneyStatus::Paused->value,
                'next_evaluation_at' => $journey->last_dispatched_at?->copy()->addHours(
                    (int) Arr::get(
                        $policyVersion->guardrail_rules,
                        'cooldown_hours',
                        config('advanced_revenue_recovery_automation.guardrails.cooldown_hours', 24),
                    ),
                ),
                'metadata' => array_merge((array) $journey->metadata, [
                    'last_blocked_reason' => 'cooldown_active',
                ]),
            ]);

            return [
                'journey' => $journey->refresh(),
                'policy_version' => $policyVersion,
                'blocked_reason' => 'cooldown_active',
            ];
        }

        $channels = $this->dispatchRules->resolveChannels($stageDefinition, $policyVersion);
        $selectedChannel = null;
        $fallbackReason = null;

        foreach ($channels as $index => $channel) {
            if ($this->dispatchRules->isChannelBlocked($case, $channel)) {
                continue;
            }

            $selectedChannel = $channel;
            $fallbackReason = $index > 0 ? 'primary_channel_blocked' : null;

            break;
        }

        if ($selectedChannel === null) {
            $journey->update([
                'journey_status' => RecoveryAutomationJourneyStatus::Paused->value,
                'metadata' => array_merge((array) $journey->metadata, [
                    'last_blocked_reason' => 'fallback_exhausted',
                ]),
            ]);

            return [
                'journey' => $journey->refresh(),
                'policy_version' => $policyVersion,
                'blocked_reason' => 'fallback_exhausted',
            ];
        }

        $dispatchKey = $this->dispatchRules->makeDispatchKey($journey->loadMissing('recoveryCase'), $stageDefinition['name'], $selectedChannel);

        $journey->update([
            'journey_status' => RecoveryAutomationJourneyStatus::Active->value,
            'current_stage' => $stageDefinition['name'],
            'current_channel' => $selectedChannel,
            'next_evaluation_at' => now(),
            'metadata' => array_merge((array) $journey->metadata, [
                'last_blocked_reason' => null,
            ]),
        ]);

        return [
            'journey' => $journey->refresh(),
            'policy_version' => $policyVersion,
            'stage_definition' => $stageDefinition,
            'channel' => $selectedChannel,
            'template_key' => $this->dispatchRules->resolveTemplateKey($policyVersion, $stageDefinition['name'], $selectedChannel),
            'fallback_reason' => $fallbackReason,
            'dispatch_key' => $dispatchKey,
        ];
    }

    public function startOrRefreshJourney(
        CasoRecuperacaoReceita $recoveryCase,
        ?RecoveryAutomationPolicyVersion $policyVersion = null,
    ): RecoveryAutomationJourney {
        $case = $recoveryCase->loadMissing('politica');
        $policyVersion ??= $this->policyResolver->resolveForCase($case);

        if ($policyVersion === null) {
            throw new \RuntimeException(sprintf(
                'Nenhuma policy version ativa encontrada para o caso de recuperacao %d.',
                $case->id,
            ));
        }

        $journey = RecoveryAutomationJourney::query()
            ->where('caso_recuperacao_receita_id', $case->id)
            ->whereIn('journey_status', [
                RecoveryAutomationJourneyStatus::Pending->value,
                RecoveryAutomationJourneyStatus::Active->value,
                RecoveryAutomationJourneyStatus::Paused->value,
            ])
            ->latest('id')
            ->first();

        if ($journey === null) {
            $journey = RecoveryAutomationJourney::query()->create([
                'caso_recuperacao_receita_id' => $case->id,
                'recovery_automation_policy_version_id' => $policyVersion->id,
                'variant_key' => 'default',
                'journey_status' => RecoveryAutomationJourneyStatus::Active->value,
                'current_stage' => $case->current_stage ?: $this->resolveStageDefinition($case, null)['name'],
                'next_evaluation_at' => now(),
                'metadata' => ['source' => 'advanced_recovery_automation'],
            ])->refresh();

            return $this->experimentService->assignJourney($journey);
        }

        $journey->update([
            'recovery_automation_policy_version_id' => $policyVersion->id,
            'current_stage' => $journey->current_stage ?: $case->current_stage ?: $this->resolveStageDefinition($case, $journey)['name'],
        ]);

        return $this->experimentService->assignJourney($journey->refresh());
    }

    /**
     * @return array{name: string, channel: string, delay_hours: int}
     */
    private function resolveStageDefinition(CasoRecuperacaoReceita $recoveryCase, ?RecoveryAutomationJourney $journey): array
    {
        /** @var Collection<int, array<string, mixed>> $stageDefinitions */
        $stageDefinitions = collect((array) $recoveryCase->politica?->stage_definitions);
        $currentStage = $journey?->current_stage ?: $recoveryCase->current_stage;
        $stageDefinition = $stageDefinitions
            ->first(fn (mixed $stage): bool => is_array($stage) && ($stage['name'] ?? null) === $currentStage);

        if (! is_array($stageDefinition)) {
            $stageDefinition = $stageDefinitions->first();
        }

        if (! is_array($stageDefinition) || ! isset($stageDefinition['name'], $stageDefinition['channel'])) {
            return [
                'name' => 'd1',
                'channel' => 'email',
                'delay_hours' => 0,
            ];
        }

        return [
            'name' => (string) $stageDefinition['name'],
            'channel' => (string) $stageDefinition['channel'],
            'delay_hours' => (int) ($stageDefinition['delay_hours'] ?? 0),
        ];
    }

    private function resolveSuppressedUntil(CasoRecuperacaoReceita $recoveryCase, RecoveryAutomationJourney $journey): ?CarbonInterface
    {
        if ($journey->suppressed_until !== null && $journey->suppressed_until->isFuture()) {
            return $journey->suppressed_until;
        }

        /** @var CompromissoPagamento|null $activePromise */
        $activePromise = $recoveryCase->compromissos
            ->where('status', 'open')
            ->first(fn (CompromissoPagamento $promise): bool => $promise->suspends_until !== null && $promise->suspends_until->isFuture());

        return $activePromise?->suspends_until;
    }
}
