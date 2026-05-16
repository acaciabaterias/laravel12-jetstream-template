<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\AlertRuleDefinition;
use App\Models\DashboardProvisioningRecord;
use App\Models\MonitoringReadinessEvidence;
use App\Models\MonitoringTargetCatalog;
use App\Support\Operations\MonitoringProvisioningStatus;
use Illuminate\Support\Collection;

class BackboneMonitoringInspectionService
{
    public function __construct(
        private readonly MonitoringAlertRuleEvaluator $monitoringAlertRuleEvaluator,
        private readonly MonitoringConsolidationEventPublisher $monitoringConsolidationEventPublisher,
    ) {}

    /**
     * @param  array{
     *     flow_name?:string|null,
     *     severity?:string|null,
     *     alert_status?:string|null,
     *     environment?:string|null,
     *     provisioning_status?:string|null,
     *     evidence_type?:string|null,
     *     limit?:int|null
     * }  $filters
     * @return array{
     *     summary: array<string, int>,
     *     targets: array<int, array<string, mixed>>,
     *     alert_rules: array<int, array<string, mixed>>,
     *     provisioning_records: array<int, array<string, mixed>>,
     *     readiness_evidences: array<int, array<string, mixed>>
     * }
     */
    public function inspect(array $filters = []): array
    {
        $flowName = (string) ($filters['flow_name'] ?? '');
        $severity = (string) ($filters['severity'] ?? '');
        $alertStatus = (string) ($filters['alert_status'] ?? '');
        $environment = (string) ($filters['environment'] ?? '');
        $provisioningStatus = (string) ($filters['provisioning_status'] ?? '');
        $evidenceType = (string) ($filters['evidence_type'] ?? '');
        $limit = (int) ($filters['limit'] ?? 25);
        $targets = MonitoringTargetCatalog::query()
            ->with('latestProbeSnapshot')
            ->when($flowName !== '', fn ($query) => $query->where('flow_name', $flowName))
            ->latest('updated_at')
            ->limit($limit)
            ->get();

        $evaluations = $this->evaluateRules([
            'flow_name' => $flowName,
            'severity' => $severity,
            'alert_status' => $alertStatus,
            'limit' => $limit,
            'publish_events' => false,
        ], $targets);
        $provisioningRecords = DashboardProvisioningRecord::query()
            ->when($environment !== '', fn ($query) => $query->where('environment', $environment))
            ->when($provisioningStatus !== '', fn ($query) => $query->where('status', $provisioningStatus))
            ->latest('updated_at')
            ->limit($limit)
            ->get();
        $readinessEvidences = MonitoringReadinessEvidence::query()
            ->with('operator')
            ->when($environment !== '', fn ($query) => $query->where('environment', $environment))
            ->when($evidenceType !== '', fn ($query) => $query->where('evidence_type', $evidenceType))
            ->latest('recorded_at')
            ->limit($limit)
            ->get();

        return [
            'summary' => [
                'healthy' => $targets->where('status.value', 'healthy')->count(),
                'degraded' => $targets->where('status.value', 'degraded')->count(),
                'unavailable' => $targets->where('status.value', 'unavailable')->count(),
                'triggered_alerts' => collect($evaluations)->where('alert_status', 'triggered')->count(),
                'provisioned_dashboards' => $provisioningRecords->where('status.value', 'applied')->count(),
                'readiness_evidences' => $readinessEvidences->count(),
            ],
            'targets' => $targets->map(fn (MonitoringTargetCatalog $target): array => [
                'id' => $target->id,
                'target_name' => $target->target_name,
                'flow_name' => $target->flow_name,
                'environment' => $target->environment,
                'collector_type' => $target->collector_type,
                'status' => $target->status->value,
                'latency_ms' => $target->latestProbeSnapshot?->latency_ms ?? 0,
                'sample_count' => $target->latestProbeSnapshot?->sample_count ?? 0,
                'scrape_status' => $target->latestProbeSnapshot?->scrape_status?->value ?? $target->status->value,
                'failure_reason' => $target->latestProbeSnapshot?->failure_reason,
            ])->values()->all(),
            'alert_rules' => $evaluations,
            'provisioning_records' => $provisioningRecords->map(fn (DashboardProvisioningRecord $record): array => [
                'id' => $record->id,
                'package_name' => $record->package_name,
                'version' => $record->version,
                'environment' => $record->environment,
                'status' => $record->status->value,
                'applied_at' => $record->applied_at?->toAtomString(),
                'validated_at' => $record->validated_at?->toAtomString(),
                'rollback_version' => $record->rollback_version,
            ])->values()->all(),
            'readiness_evidences' => $readinessEvidences->map(fn (MonitoringReadinessEvidence $evidence): array => [
                'id' => $evidence->id,
                'environment' => $evidence->environment,
                'evidence_type' => $evidence->evidence_type,
                'result_status' => $evidence->result_status->value,
                'operator' => $evidence->operator?->name,
                'recorded_at' => $evidence->recorded_at?->toAtomString(),
                'payload' => $evidence->payload ?? [],
            ])->values()->all(),
        ];
    }

    /**
     * @param  array{
     *     flow_name?:string|null,
     *     severity?:string|null,
     *     alert_status?:string|null,
     *     limit?:int|null,
     *     publish_events?:bool
     * }  $filters
     * @return array<int, array<string, mixed>>
     */
    public function evaluateRules(array $filters = [], ?Collection $targets = null): array
    {
        $flowName = (string) ($filters['flow_name'] ?? '');
        $severity = (string) ($filters['severity'] ?? '');
        $alertStatusFilter = (string) ($filters['alert_status'] ?? '');
        $limit = (int) ($filters['limit'] ?? 25);
        $publishEvents = (bool) ($filters['publish_events'] ?? false);
        $targets = $targets instanceof Collection ? $targets : MonitoringTargetCatalog::query()
            ->with('latestProbeSnapshot')
            ->when($flowName !== '', fn ($query) => $query->where('flow_name', $flowName))
            ->get();

        $targetsByFlow = $targets->groupBy('flow_name');

        return AlertRuleDefinition::query()
            ->when($flowName !== '', fn ($query) => $query->where('flow_name', $flowName))
            ->when($severity !== '', fn ($query) => $query->where('severity', $severity))
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(function (AlertRuleDefinition $rule) use ($targetsByFlow, $alertStatusFilter, $publishEvents): ?array {
                $matchedTargets = [];
                $flowTargets = $targetsByFlow->get($rule->flow_name, collect());

                foreach ($flowTargets as $target) {
                    $evaluation = $this->monitoringAlertRuleEvaluator->evaluate($rule, [
                        'flow_name' => $target->flow_name,
                        'target_name' => $target->target_name,
                        'environment' => $target->environment,
                        'latency_ms' => $target->latestProbeSnapshot?->latency_ms ?? 0,
                        'sample_count' => $target->latestProbeSnapshot?->sample_count ?? 0,
                        'collector_unavailable' => ($target->latestProbeSnapshot?->scrape_status?->value ?? $target->status->value) === 'unavailable',
                        'scrape_status' => $target->latestProbeSnapshot?->scrape_status?->value ?? $target->status->value,
                    ]);

                    if ($evaluation['triggered']) {
                        $matchedTargets[] = [
                            'target_name' => $target->target_name,
                            'environment' => $target->environment,
                            'actual' => $evaluation['actual'],
                            'reason' => $evaluation['reason'],
                        ];
                    }
                }

                $alertStatus = $this->resolveAlertStatus($rule, $matchedTargets, $flowTargets->count());

                if ($alertStatusFilter !== '' && $alertStatus !== $alertStatusFilter) {
                    return null;
                }

                if ($publishEvents && $alertStatus === 'triggered') {
                    $this->publishTriggeredEvent($rule, $matchedTargets);
                }

                return [
                    'id' => $rule->id,
                    'flow_name' => $rule->flow_name,
                    'rule_name' => $rule->rule_name,
                    'severity' => $rule->severity->value,
                    'version' => $rule->version,
                    'condition_summary' => $rule->condition_summary,
                    'rule_status' => $rule->status->value,
                    'alert_status' => $alertStatus,
                    'matched_targets' => $matchedTargets,
                    'metadata' => $rule->metadata ?? [],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{target_name:string, environment:string, actual:mixed, reason:string}>  $matchedTargets
     */
    private function publishTriggeredEvent(AlertRuleDefinition $rule, array $matchedTargets): void
    {
        $this->monitoringConsolidationEventPublisher->publish(
            config('monitoring_consolidation.alerts.material_event_type', 'MONITORAMENTO_DEGRADADO'),
            sprintf('%s:%s:%s', $rule->flow_name, $rule->rule_name, $rule->version),
            [
                'flow_name' => $rule->flow_name,
                'rule_name' => $rule->rule_name,
                'severity' => $rule->severity->value,
                'version' => $rule->version,
                'matched_targets' => $matchedTargets,
            ],
            ['platform', 'support'],
            [
                'type' => 'monitoring-alert-materialization',
                'rule_status' => $rule->status->value,
            ],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $matchedTargets
     */
    private function resolveAlertStatus(AlertRuleDefinition $rule, array $matchedTargets, int $targetCount): string
    {
        if ($rule->status !== MonitoringProvisioningStatus::Applied) {
            return 'inactive';
        }

        if ($targetCount === 0) {
            return 'unknown';
        }

        return $matchedTargets !== [] ? 'triggered' : 'clear';
    }
}
