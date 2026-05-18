<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Models\RecoveryAutomationViolation;

class AdvancedRecoveryAutomationInspectionService
{
    /**
     * @param  array{policy_status?:string|null,severity?:string|null,limit?:int|null}  $filters
     * @return array{
     *     summary: array<string, int>,
     *     policies: array<int, array<string, mixed>>,
     *     violations: array<int, array<string, mixed>>,
     *     journeys: array<int, array<string, mixed>>,
     *     rollback_contexts: array<int, array<string, mixed>>
     * }
     */
    public function inspect(array $filters = []): array
    {
        $limit = (int) ($filters['limit'] ?? 25);
        $policyStatus = (string) ($filters['policy_status'] ?? '');
        $severity = (string) ($filters['severity'] ?? '');

        $policies = RecoveryAutomationPolicyVersion::query()
            ->with(['experiments', 'violations'])
            ->when($policyStatus !== '', fn ($query) => $query->where('status', $policyStatus))
            ->latest('activation_completed_at')
            ->latest('id')
            ->limit($limit)
            ->get();

        $violations = RecoveryAutomationViolation::query()
            ->with(['policyVersion', 'journey'])
            ->when($severity !== '', fn ($query) => $query->where('severity', $severity))
            ->latest('detected_at')
            ->limit($limit)
            ->get();

        $journeys = RecoveryAutomationJourney::query()
            ->with(['policyVersion', 'experiment'])
            ->latest('updated_at')
            ->limit($limit)
            ->get();

        $rollbackPolicies = $policies
            ->filter(fn (RecoveryAutomationPolicyVersion $policyVersion): bool => $policyVersion->status->value === 'rolled_back');

        return [
            'summary' => [
                'active_policies' => RecoveryAutomationPolicyVersion::query()->where('status', 'active')->count(),
                'rolled_back_policies' => RecoveryAutomationPolicyVersion::query()->where('status', 'rolled_back')->count(),
                'open_violations' => RecoveryAutomationViolation::query()->where('resolution_status', 'open')->count(),
                'critical_violations' => RecoveryAutomationViolation::query()
                    ->where('resolution_status', 'open')
                    ->where('severity', 'critical')
                    ->count(),
                'affected_journeys' => RecoveryAutomationJourney::query()
                    ->whereNotNull('rollback_marked_at')
                    ->count(),
            ],
            'policies' => $policies->map(fn (RecoveryAutomationPolicyVersion $policyVersion): array => [
                'id' => $policyVersion->id,
                'slug' => $policyVersion->slug,
                'name' => $policyVersion->name,
                'status' => $policyVersion->status->value,
                'activation_completed_at' => $policyVersion->activation_completed_at?->toAtomString(),
                'experiments_count' => $policyVersion->experiments->count(),
                'violations_count' => $policyVersion->violations->count(),
                'metadata' => $policyVersion->metadata,
            ])->values()->all(),
            'violations' => $violations->map(fn (RecoveryAutomationViolation $violation): array => [
                'id' => $violation->id,
                'policy_version_id' => $violation->recovery_automation_policy_version_id,
                'journey_id' => $violation->recovery_automation_journey_id,
                'violation_type' => $violation->violation_type,
                'severity' => $violation->severity->value,
                'resolution_status' => $violation->resolution_status,
                'summary' => $violation->summary,
                'detected_at' => $violation->detected_at?->toAtomString(),
                'evidence_payload' => $violation->evidence_payload,
            ])->values()->all(),
            'journeys' => $journeys->map(fn (RecoveryAutomationJourney $journey): array => [
                'id' => $journey->id,
                'policy_version_id' => $journey->recovery_automation_policy_version_id,
                'experiment_id' => $journey->recovery_automation_experiment_id,
                'variant_key' => $journey->variant_key,
                'journey_status' => $journey->journey_status->value,
                'rollback_marked_at' => $journey->rollback_marked_at?->toAtomString(),
                'metadata' => $journey->metadata,
            ])->values()->all(),
            'rollback_contexts' => $rollbackPolicies->map(fn (RecoveryAutomationPolicyVersion $policyVersion): array => [
                'policy_version_id' => $policyVersion->id,
                'status' => $policyVersion->status->value,
                'rollback' => (array) ($policyVersion->metadata['rollback'] ?? []),
            ])->values()->all(),
        ];
    }
}
