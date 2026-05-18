<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Models\RecoveryAutomationViolation;
use App\Support\Billing\RecoveryAutomationJourneyStatus;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use RuntimeException;

class AdvancedRecoveryAutomationRollbackService
{
    public function __construct(
        private readonly AdvancedRecoveryAutomationRollbackRules $rollbackRules,
        private readonly AdvancedRevenueRecoveryAutomationEventPublisher $eventPublisher,
    ) {}

    public function rollback(RecoveryAutomationPolicyVersion $policyVersion, string $reason, ?int $operatorId): RecoveryAutomationPolicyVersion
    {
        $policyVersion->loadMissing('violations');

        /** @var Collection<int, RecoveryAutomationPolicyVersion> $publishedPolicies */
        $publishedPolicies = RecoveryAutomationPolicyVersion::query()
            ->whereIn('status', [
                RecoveryAutomationPolicyStatus::Active->value,
                RecoveryAutomationPolicyStatus::Superseded->value,
                RecoveryAutomationPolicyStatus::RolledBack->value,
            ])
            ->orderByDesc('activation_completed_at')
            ->orderByDesc('id')
            ->get();

        $restoredPolicy = $this->rollbackRules->findRestorablePolicy($publishedPolicies, $policyVersion->id);
        $openCriticalViolations = RecoveryAutomationViolation::query()
            ->where('recovery_automation_policy_version_id', $policyVersion->id)
            ->where('resolution_status', 'open')
            ->where('severity', 'critical')
            ->count();

        if (! $this->rollbackRules->canRollback($policyVersion, $restoredPolicy, $openCriticalViolations)) {
            throw new RuntimeException('Rollback indisponivel para esta politica sem baseline elegivel ou sem violacao critica aberta.');
        }

        /** @var ConnectionInterface $connection */
        $connection = RecoveryAutomationPolicyVersion::query()->getModel()->getConnection();

        return $connection->transaction(function () use ($policyVersion, $restoredPolicy, $operatorId, $reason): RecoveryAutomationPolicyVersion {
            $affectedJourneys = RecoveryAutomationJourney::query()
                ->where('recovery_automation_policy_version_id', $policyVersion->id)
                ->whereIn('journey_status', [
                    RecoveryAutomationJourneyStatus::Pending->value,
                    RecoveryAutomationJourneyStatus::Active->value,
                    RecoveryAutomationJourneyStatus::Paused->value,
                ])
                ->get();

            $affectedJourneys->each(function (RecoveryAutomationJourney $journey) use ($restoredPolicy, $reason): void {
                $journey->forceFill([
                    'journey_status' => RecoveryAutomationJourneyStatus::RolledBack->value,
                    'rollback_marked_at' => now(),
                    'metadata' => array_merge((array) $journey->metadata, [
                        'rollback_context' => [
                            'rolled_back_from_policy_version_id' => $journey->recovery_automation_policy_version_id,
                            'restored_policy_version_id' => $restoredPolicy?->id,
                            'reason' => $reason,
                            'marked_at' => now()->toIso8601String(),
                        ],
                    ]),
                ])->save();
            });

            $policyVersion->forceFill([
                'status' => RecoveryAutomationPolicyStatus::RolledBack->value,
                'rolled_back_by' => $operatorId,
                'metadata' => array_merge((array) $policyVersion->metadata, [
                    'rollback' => [
                        'reason' => $reason,
                        'restored_policy_version_id' => $restoredPolicy?->id,
                        'affected_journeys' => $affectedJourneys->count(),
                        'rolled_back_at' => now()->toIso8601String(),
                    ],
                ]),
            ])->save();

            $restoredPolicy?->forceFill([
                'status' => RecoveryAutomationPolicyStatus::Active->value,
                'activation_completed_at' => now(),
                'metadata' => array_merge((array) $restoredPolicy->metadata, [
                    'restored_from_rollback' => [
                        'policy_version_id' => $policyVersion->id,
                        'reason' => $reason,
                        'restored_at' => now()->toIso8601String(),
                    ],
                ]),
            ])->save();

            RecoveryAutomationViolation::query()
                ->where('recovery_automation_policy_version_id', $policyVersion->id)
                ->where('resolution_status', 'open')
                ->update([
                    'resolution_status' => 'rolled_back',
                    'resolved_at' => now(),
                    'resolved_by' => $operatorId,
                ]);

            $this->eventPublisher->publish(
                eventType: 'ROLLBACK_AUTOMACAO_RECUPERACAO_EXECUTADO',
                recoveryCase: null,
                payload: [
                    'policy_version_id' => $policyVersion->id,
                    'policy_slug' => $policyVersion->slug,
                    'journey_id' => null,
                    'dispatch_id' => null,
                    'case_id' => null,
                    'variant_key' => null,
                    'channel' => null,
                    'status' => RecoveryAutomationPolicyStatus::RolledBack->value,
                    'occurred_at' => now()->toIso8601String(),
                    'metadata' => [
                        'restored_policy_version_id' => $restoredPolicy?->id,
                        'affected_journeys' => $affectedJourneys->count(),
                        'reason' => $reason,
                    ],
                ],
                consumers: config('advanced_revenue_recovery_automation.events.default_consumers', ['platform', 'recovery', 'analytics']),
                schemaDefinition: [
                    'policy_version_id' => 'integer',
                    'policy_slug' => 'string',
                    'status' => 'string',
                    'occurred_at' => 'string',
                    'metadata' => 'array',
                ],
            );

            return $policyVersion->refresh();
        });
    }
}
