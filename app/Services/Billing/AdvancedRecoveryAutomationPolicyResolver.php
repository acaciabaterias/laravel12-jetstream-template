<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CasoRecuperacaoReceita;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AdvancedRecoveryAutomationPolicyResolver
{
    public function resolveForCase(CasoRecuperacaoReceita $recoveryCase): ?RecoveryAutomationPolicyVersion
    {
        $case = $recoveryCase->loadMissing('fatura', 'politica');

        /** @var Collection<int, RecoveryAutomationPolicyVersion> $policies */
        $policies = RecoveryAutomationPolicyVersion::query()
            ->where('status', RecoveryAutomationPolicyStatus::Active->value)
            ->orderByDesc('activation_started_at')
            ->orderByDesc('id')
            ->get();

        return $policies->first(fn (RecoveryAutomationPolicyVersion $policyVersion): bool => $this->matchesScope($policyVersion, $case));
    }

    private function matchesScope(RecoveryAutomationPolicyVersion $policyVersion, CasoRecuperacaoReceita $recoveryCase): bool
    {
        $scopeFilters = (array) $policyVersion->scope_filters;
        $severityFilter = Arr::get($scopeFilters, 'severity');

        if ($severityFilter !== null) {
            $allowedSeverities = is_array($severityFilter) ? $severityFilter : [$severityFilter];

            if (! in_array($recoveryCase->severity?->value, array_map('strval', $allowedSeverities), true)) {
                return false;
            }
        }

        $revenueRecoveryPolicyId = Arr::get($scopeFilters, 'revenue_recovery_policy_id');

        if ($revenueRecoveryPolicyId !== null && (int) $revenueRecoveryPolicyId !== (int) $recoveryCase->politica_recuperacao_receita_id) {
            return false;
        }

        $minimumOverdueDays = Arr::get($scopeFilters, 'minimum_overdue_days');
        $maximumOverdueDays = Arr::get($scopeFilters, 'maximum_overdue_days');
        $daysOverdue = $this->resolveDaysOverdue($recoveryCase);

        if ($minimumOverdueDays !== null && $daysOverdue < (int) $minimumOverdueDays) {
            return false;
        }

        if ($maximumOverdueDays !== null && $daysOverdue > (int) $maximumOverdueDays) {
            return false;
        }

        return true;
    }

    private function resolveDaysOverdue(CasoRecuperacaoReceita $recoveryCase): int
    {
        if ($recoveryCase->fatura?->vencimento === null) {
            return 0;
        }

        return (int) (now()->startOfDay()->diffInDays($recoveryCase->fatura->vencimento, false) * -1);
    }
}
