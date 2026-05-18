<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CasoRecuperacaoReceita;
use App\Models\RecoveryAutomationJourney;
use App\Support\Billing\RecoveryAutomationJourneyStatus;
use Illuminate\Database\Eloquent\Builder;

class AdvancedRecoveryAutomationJourneyQueryService
{
    /**
     * @return Builder<RecoveryAutomationJourney>
     */
    public function dueJourneys(): Builder
    {
        return RecoveryAutomationJourney::query()
            ->with(['recoveryCase', 'policyVersion'])
            ->whereIn('journey_status', [
                RecoveryAutomationJourneyStatus::Pending->value,
                RecoveryAutomationJourneyStatus::Active->value,
            ])
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('next_evaluation_at')
                    ->orWhere('next_evaluation_at', '<=', now());
            });
    }

    /**
     * @return Builder<RecoveryAutomationJourney>
     */
    public function suppressedJourneys(): Builder
    {
        return RecoveryAutomationJourney::query()
            ->with(['recoveryCase', 'policyVersion'])
            ->where('journey_status', RecoveryAutomationJourneyStatus::Paused->value)
            ->whereNotNull('suppressed_until')
            ->where('suppressed_until', '>', now());
    }

    /**
     * @return Builder<CasoRecuperacaoReceita>
     */
    public function dueCases(): Builder
    {
        return CasoRecuperacaoReceita::query()
            ->whereIn('id', $this->dueJourneys()->select('caso_recuperacao_receita_id'));
    }
}
