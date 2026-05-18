<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\RecoveryAutomationPolicyVersion;
use App\Support\Billing\RecoveryAutomationViolationSeverity;
use Illuminate\Support\Collection;

class AdvancedRecoveryAutomationRollbackRules
{
    public function classifySeverity(string $violationType, int $affectedJourneys = 0, float $regressionRatio = 0.0): RecoveryAutomationViolationSeverity
    {
        if ($violationType === 'performance_regression' && $regressionRatio >= 0.25) {
            return RecoveryAutomationViolationSeverity::Critical;
        }

        if (in_array($violationType, ['duplicate_dispatch', 'fallback_exhausted'], true) || $affectedJourneys >= 5) {
            return RecoveryAutomationViolationSeverity::High;
        }

        if ($violationType === 'out_of_window' || $affectedJourneys >= 2) {
            return RecoveryAutomationViolationSeverity::Medium;
        }

        return RecoveryAutomationViolationSeverity::Low;
    }

    public function canRollback(
        RecoveryAutomationPolicyVersion $candidatePolicy,
        ?RecoveryAutomationPolicyVersion $restoredPolicy,
        int $openCriticalViolations,
    ): bool {
        if ($restoredPolicy === null) {
            return false;
        }

        if ($candidatePolicy->status->value !== 'active') {
            return false;
        }

        if ($openCriticalViolations <= 0) {
            return false;
        }

        return in_array($restoredPolicy->status->value, ['superseded', 'rolled_back'], true);
    }

    /**
     * @param  Collection<int, RecoveryAutomationPolicyVersion>  $policies
     */
    public function findRestorablePolicy(Collection $policies, int $currentPolicyId): ?RecoveryAutomationPolicyVersion
    {
        return $policies
            ->reject(fn (RecoveryAutomationPolicyVersion $policyVersion): bool => $policyVersion->id === $currentPolicyId)
            ->sortByDesc(fn (RecoveryAutomationPolicyVersion $policyVersion): int => $policyVersion->activation_completed_at?->getTimestamp() ?? 0)
            ->first();
    }
}
