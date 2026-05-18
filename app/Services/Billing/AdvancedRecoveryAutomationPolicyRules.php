<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\RecoveryAutomationExperiment;
use App\Models\RecoveryAutomationPolicyVersion;
use Illuminate\Support\Arr;

class AdvancedRecoveryAutomationPolicyRules
{
    /**
     * @return array{passed: bool, messages: array<int, string>}
     */
    public function validatePublication(RecoveryAutomationPolicyVersion $policyVersion): array
    {
        $messages = [];
        $guardrails = (array) $policyVersion->guardrail_rules;
        $fallbackMatrix = (array) $policyVersion->fallback_matrix;

        if (blank($policyVersion->slug) || blank($policyVersion->name)) {
            $messages[] = 'Policy version precisa de slug e nome antes da publicacao.';
        }

        foreach (['max_dispatches_per_day', 'cooldown_hours', 'suppression_hours'] as $requiredKey) {
            if (! Arr::has($guardrails, $requiredKey)) {
                $messages[] = sprintf('Guardrail obrigatorio ausente: %s.', $requiredKey);
            }
        }

        $stageChannels = Arr::get($fallbackMatrix, 'stage_channels', []);
        $fallbacks = Arr::get($fallbackMatrix, 'fallbacks', []);

        if (! is_array($stageChannels) || $stageChannels === []) {
            $messages[] = 'Fallback matrix precisa definir ao menos um stage_channels.';
        }

        if (! is_array($fallbacks) || $fallbacks === []) {
            $messages[] = 'Fallback matrix precisa definir uma ordem de fallback.';
        }

        return [
            'passed' => $messages === [],
            'messages' => $messages,
        ];
    }

    /**
     * @return array{passed: bool, messages: array<int, string>}
     */
    public function validateExperiment(RecoveryAutomationExperiment $experiment): array
    {
        $messages = [];
        $variants = (array) $experiment->variant_definitions;
        $controlRatio = (float) $experiment->control_ratio;

        if ($experiment->name === '') {
            $messages[] = 'Experimento precisa de um nome.';
        }

        if ($controlRatio < 0 || $controlRatio > 1) {
            $messages[] = 'Control ratio precisa ficar entre 0 e 1.';
        }

        $candidateVariants = collect($variants)
            ->reject(fn (mixed $definition, string $key): bool => $key === 'holdout' || $key === 'control')
            ->filter(fn (mixed $definition): bool => is_array($definition) && $definition !== []);

        if ($candidateVariants->isEmpty()) {
            $messages[] = 'Experimento precisa de pelo menos uma variante ativa fora do holdout.';
        }

        return [
            'passed' => $messages === [],
            'messages' => $messages,
        ];
    }

    /**
     * @param  array<string, mixed>  $allocationRules
     * @param  array<string, mixed>  $variantDefinitions
     */
    public function assignVariantKey(
        int $caseId,
        float $controlRatio,
        array $allocationRules,
        array $variantDefinitions,
    ): string {
        $forcedAssignments = Arr::get($allocationRules, 'forced_assignments', []);

        if (is_array($forcedAssignments) && array_key_exists((string) $caseId, $forcedAssignments)) {
            return (string) $forcedAssignments[(string) $caseId];
        }

        $bucket = (abs(crc32(sprintf('advanced-recovery:%d', $caseId))) % 1000) / 1000;

        if ($bucket < $controlRatio) {
            return 'holdout';
        }

        $variants = collect($variantDefinitions)
            ->reject(fn (mixed $definition, string $key): bool => in_array($key, ['holdout', 'control'], true))
            ->mapWithKeys(fn (mixed $definition, string $key): array => [$key => is_array($definition) ? $definition : []]);

        if ($variants->isEmpty()) {
            return 'holdout';
        }

        $orderedKeys = $variants->keys()->values();
        $selectedIndex = abs(crc32(sprintf('advanced-recovery-variant:%d', $caseId))) % $orderedKeys->count();

        return (string) $orderedKeys[$selectedIndex];
    }
}
