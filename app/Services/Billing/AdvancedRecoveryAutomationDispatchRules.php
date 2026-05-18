<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CasoRecuperacaoReceita;
use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;

class AdvancedRecoveryAutomationDispatchRules
{
    public function makeDispatchKey(RecoveryAutomationJourney $journey, string $stageKey, string $channel): string
    {
        return sha1(sprintf(
            'advanced-recovery:%d:%d:%s:%s',
            $journey->id,
            $journey->recoveryCase?->fatura_saas_id ?? 0,
            $stageKey,
            $channel,
        ));
    }

    public function isWithinCooldown(
        RecoveryAutomationJourney $journey,
        RecoveryAutomationPolicyVersion $policyVersion,
        ?CarbonInterface $reference = null,
    ): bool {
        $cooldownHours = (int) Arr::get(
            $policyVersion->guardrail_rules,
            'cooldown_hours',
            config('advanced_revenue_recovery_automation.guardrails.cooldown_hours', 24),
        );

        if ($cooldownHours <= 0 || $journey->last_dispatched_at === null) {
            return false;
        }

        $reference ??= now();

        return $journey->last_dispatched_at->copy()->addHours($cooldownHours)->isAfter($reference);
    }

    public function resolveSuppressionReason(
        CasoRecuperacaoReceita $recoveryCase,
        RecoveryAutomationJourney $journey,
        ?CarbonInterface $reference = null,
    ): ?string {
        $reference ??= now();

        if ($journey->suppressed_until !== null && $journey->suppressed_until->isAfter($reference)) {
            return 'journey_suppressed';
        }

        $activePromise = $recoveryCase->compromissos()
            ->where('status', 'open')
            ->where('suspends_until', '>=', $reference)
            ->latest('suspends_until')
            ->first();

        if ($activePromise !== null) {
            return 'active_promise';
        }

        return null;
    }

    /**
     * @param  array{name: string, channel: string, delay_hours?: int}  $stageDefinition
     * @return array<int, string>
     */
    public function resolveChannels(array $stageDefinition, RecoveryAutomationPolicyVersion $policyVersion): array
    {
        $stageChannels = Arr::get($policyVersion->fallback_matrix, sprintf('stage_channels.%s', $stageDefinition['name']));
        $candidateChannels = is_array($stageChannels)
            ? $stageChannels
            : array_merge(
                [$stageDefinition['channel']],
                (array) Arr::get(
                    $policyVersion->fallback_matrix,
                    'fallbacks',
                    config('advanced_revenue_recovery_automation.fallback.default_order', []),
                ),
            );

        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $channel): string => trim((string) $channel),
            $candidateChannels,
        ))));
    }

    public function isChannelBlocked(CasoRecuperacaoReceita $recoveryCase, string $channel): bool
    {
        $blockedChannels = Arr::get($recoveryCase->metadata, 'blocked_channels', []);

        if (! is_array($blockedChannels)) {
            return false;
        }

        return in_array($channel, $blockedChannels, true);
    }

    public function resolveTemplateKey(
        RecoveryAutomationPolicyVersion $policyVersion,
        string $stageKey,
        string $channel,
    ): string {
        $templateKey = Arr::get($policyVersion->fallback_matrix, sprintf('templates.%s.%s', $stageKey, $channel));

        if (is_string($templateKey) && $templateKey !== '') {
            return $templateKey;
        }

        return sprintf('recovery-%s-%s', $stageKey, str_replace('_', '-', $channel));
    }
}
