<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\RecoveryAutomationExperiment;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;

class AdvancedRecoveryAutomationPublicationService
{
    public function __construct(
        private readonly AdvancedRecoveryAutomationPolicyRules $policyRules,
        private readonly AdvancedRecoveryAutomationExperimentService $experimentService,
        private readonly AdvancedRevenueRecoveryAutomationEventPublisher $eventPublisher,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createDraft(array $attributes, ?int $operatorId): RecoveryAutomationPolicyVersion
    {
        return RecoveryAutomationPolicyVersion::query()->create([
            'slug' => (string) $attributes['slug'],
            'name' => (string) $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'status' => RecoveryAutomationPolicyStatus::Draft->value,
            'scope_filters' => (array) ($attributes['scope_filters'] ?? []),
            'guardrail_rules' => (array) ($attributes['guardrail_rules'] ?? []),
            'fallback_matrix' => (array) ($attributes['fallback_matrix'] ?? []),
            'created_by' => $operatorId,
            'metadata' => array_merge((array) ($attributes['metadata'] ?? []), [
                'source' => 'advanced_recovery_automation_manager',
            ]),
        ])->refresh();
    }

    public function publish(
        RecoveryAutomationPolicyVersion $policyVersion,
        ?RecoveryAutomationExperiment $experiment,
        ?int $operatorId,
    ): RecoveryAutomationPolicyVersion {
        $validation = $this->policyRules->validatePublication($policyVersion);

        if (! $validation['passed']) {
            throw new RuntimeException(implode(' ', $validation['messages']));
        }

        /** @var ConnectionInterface $connection */
        $connection = RecoveryAutomationPolicyVersion::query()->getModel()->getConnection();

        return $connection->transaction(function () use ($policyVersion, $experiment, $operatorId, $validation): RecoveryAutomationPolicyVersion {
            $activePolicies = RecoveryAutomationPolicyVersion::query()
                ->where('status', RecoveryAutomationPolicyStatus::Active->value)
                ->where('id', '!=', $policyVersion->id)
                ->get();

            foreach ($activePolicies as $activePolicy) {
                $activePolicy->forceFill([
                    'status' => RecoveryAutomationPolicyStatus::Superseded->value,
                    'superseded_by_policy_version_id' => $policyVersion->id,
                ])->save();
            }

            $policyVersion->forceFill([
                'status' => RecoveryAutomationPolicyStatus::Active->value,
                'approved_by' => $operatorId,
                'activation_started_at' => $policyVersion->activation_started_at ?? now(),
                'activation_completed_at' => now(),
                'metadata' => array_merge((array) $policyVersion->metadata, [
                    'validation_messages' => $validation['messages'],
                ]),
            ])->save();

            if ($experiment !== null) {
                $experiment = $this->experimentService->activate($experiment);
            }

            $publishedPolicy = $policyVersion->refresh();
            $this->publishPolicyEvent($publishedPolicy, $experiment);

            if ($experiment !== null) {
                $this->experimentService->publishActivationEvent($experiment);
            }

            return $publishedPolicy;
        });
    }

    private function publishPolicyEvent(
        RecoveryAutomationPolicyVersion $policyVersion,
        ?RecoveryAutomationExperiment $experiment,
    ): void {
        $this->eventPublisher->publish(
            eventType: 'POLITICA_AUTOMACAO_RECUPERACAO_PUBLICADA',
            recoveryCase: null,
            payload: [
                'policy_version_id' => $policyVersion->id,
                'policy_slug' => $policyVersion->slug,
                'journey_id' => null,
                'dispatch_id' => null,
                'case_id' => null,
                'variant_key' => $experiment !== null ? 'experiment_attached' : 'default',
                'channel' => null,
                'status' => $policyVersion->status->value,
                'occurred_at' => now()->toIso8601String(),
                'metadata' => [
                    'experiment_id' => $experiment?->id,
                    'approved_by' => $policyVersion->approved_by,
                ],
            ],
            consumers: config('advanced_revenue_recovery_automation.events.default_consumers', ['platform', 'recovery', 'analytics']),
            schemaDefinition: [
                'policy_version_id' => 'integer',
                'policy_slug' => 'string',
                'variant_key' => 'string',
                'status' => 'string',
                'occurred_at' => 'string',
                'metadata' => 'array',
            ],
        );
    }
}
