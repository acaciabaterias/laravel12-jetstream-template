<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\RecoveryAutomationExperiment;
use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Support\Billing\RecoveryAutomationExperimentStatus;
use App\Support\Billing\RecoveryAutomationJourneyStatus;
use RuntimeException;

class AdvancedRecoveryAutomationExperimentService
{
    public function __construct(
        private readonly AdvancedRecoveryAutomationPolicyRules $policyRules,
        private readonly AdvancedRevenueRecoveryAutomationEventPublisher $eventPublisher,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function registerDraft(
        RecoveryAutomationPolicyVersion $policyVersion,
        array $attributes,
        ?int $operatorId,
    ): RecoveryAutomationExperiment {
        return $policyVersion->experiments()->create([
            'name' => (string) ($attributes['name'] ?? 'Experiment'),
            'status' => RecoveryAutomationExperimentStatus::Draft->value,
            'allocation_rules' => (array) ($attributes['allocation_rules'] ?? []),
            'control_ratio' => (float) ($attributes['control_ratio'] ?? config('advanced_revenue_recovery_automation.experiments.default_control_ratio', 0.10)),
            'variant_definitions' => (array) ($attributes['variant_definitions'] ?? []),
            'created_by' => $operatorId,
            'metadata' => array_merge((array) ($attributes['metadata'] ?? []), [
                'source' => 'advanced_recovery_automation_manager',
            ]),
        ])->refresh();
    }

    public function activate(RecoveryAutomationExperiment $experiment): RecoveryAutomationExperiment
    {
        $validation = $this->policyRules->validateExperiment($experiment);

        if (! $validation['passed']) {
            throw new RuntimeException(implode(' ', $validation['messages']));
        }

        $experiment->forceFill([
            'status' => RecoveryAutomationExperimentStatus::Running->value,
            'started_at' => now(),
            'metadata' => array_merge((array) $experiment->metadata, [
                'validation_messages' => $validation['messages'],
            ]),
        ])->save();

        return $experiment->refresh();
    }

    public function assignJourney(RecoveryAutomationJourney $journey): RecoveryAutomationJourney
    {
        $journey->loadMissing('policyVersion', 'recoveryCase', 'experiment');

        if (filled($journey->variant_key) && $journey->variant_key !== 'default' && $journey->recovery_automation_experiment_id !== null) {
            return $journey;
        }

        $experiment = $journey->experiment;

        if ($experiment === null) {
            $experiment = $journey->policyVersion
                ->experiments()
                ->where('status', RecoveryAutomationExperimentStatus::Running->value)
                ->latest('started_at')
                ->latest('id')
                ->first();
        }

        if ($experiment === null) {
            return $journey;
        }

        $variantKey = $this->policyRules->assignVariantKey(
            caseId: (int) $journey->caso_recuperacao_receita_id,
            controlRatio: (float) $experiment->control_ratio,
            allocationRules: (array) $experiment->allocation_rules,
            variantDefinitions: (array) $experiment->variant_definitions,
        );

        $journey->forceFill([
            'recovery_automation_experiment_id' => $experiment->id,
            'variant_key' => $variantKey,
            'journey_status' => $variantKey === 'holdout'
                ? RecoveryAutomationJourneyStatus::Paused->value
                : $journey->journey_status->value,
            'metadata' => array_merge((array) $journey->metadata, [
                'experiment_assignment' => [
                    'experiment_id' => $experiment->id,
                    'variant_key' => $variantKey,
                    'assigned_at' => now()->toIso8601String(),
                ],
            ]),
        ])->save();

        return $journey->refresh();
    }

    public function publishActivationEvent(RecoveryAutomationExperiment $experiment): void
    {
        $experiment->loadMissing('policyVersion');

        $this->eventPublisher->publish(
            eventType: 'POLITICA_AUTOMACAO_RECUPERACAO_PUBLICADA',
            recoveryCase: null,
            payload: [
                'policy_version_id' => $experiment->recovery_automation_policy_version_id,
                'policy_slug' => $experiment->policyVersion?->slug,
                'journey_id' => null,
                'dispatch_id' => null,
                'case_id' => null,
                'variant_key' => 'experiment_running',
                'channel' => null,
                'status' => $experiment->status->value,
                'occurred_at' => now()->toIso8601String(),
                'metadata' => [
                    'experiment_id' => $experiment->id,
                    'experiment_name' => $experiment->name,
                ],
            ],
            consumers: config('advanced_revenue_recovery_automation.events.default_consumers', ['platform', 'recovery', 'analytics']),
            schemaDefinition: [
                'policy_version_id' => 'integer',
                'policy_slug' => 'string|null',
                'variant_key' => 'string',
                'status' => 'string',
                'occurred_at' => 'string',
                'metadata' => 'array',
            ],
        );
    }
}
