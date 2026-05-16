<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\MonitoringProbeSnapshot;
use App\Models\MonitoringTargetCatalog;
use App\Support\Operations\MonitoringSeverity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class MonitoringReadinessService
{
    public function __construct(
        private readonly MonitoringScrapeHealthClassifier $monitoringScrapeHealthClassifier,
        private readonly MonitoringConsolidationEventPublisher $monitoringConsolidationEventPublisher,
    ) {}

    /**
     * @return array<string, int>
     */
    public function summarize(): array
    {
        return [
            'healthy' => MonitoringTargetCatalog::query()->where('status', 'healthy')->count(),
            'degraded' => MonitoringTargetCatalog::query()->where('status', 'degraded')->count(),
            'unavailable' => MonitoringTargetCatalog::query()->where('status', 'unavailable')->count(),
        ];
    }

    /**
     * @return Collection<int, MonitoringTargetCatalog>
     */
    public function latestTargets(?string $flowName = null, int $limit = 12): Collection
    {
        return MonitoringTargetCatalog::query()
            ->with('latestProbeSnapshot')
            ->when($flowName !== null && $flowName !== '', fn ($query) => $query->where('flow_name', $flowName))
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, MonitoringProbeSnapshot>
     */
    public function refreshAll(): Collection
    {
        return MonitoringTargetCatalog::query()
            ->get()
            ->map(fn (MonitoringTargetCatalog $target): MonitoringProbeSnapshot => $this->refreshTarget($target));
    }

    public function refreshTarget(MonitoringTargetCatalog $target): MonitoringProbeSnapshot
    {
        $startedAt = microtime(true);
        $collectorUnavailable = false;
        $failureReason = null;
        $sampleCount = 0;

        try {
            $response = Http::timeout(5)->acceptJson()->get($target->endpoint);
            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

            if (! $response->successful()) {
                $collectorUnavailable = true;
                $failureReason = sprintf('http_status_%s', $response->status());
            } else {
                $payload = $response->json();
                $sampleCount = is_array($payload)
                    ? (int) ($payload['sample_count'] ?? count($payload))
                    : 1;
            }
        } catch (\Throwable $exception) {
            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);
            $collectorUnavailable = true;
            $failureReason = $exception->getMessage();
        }

        $classification = $this->monitoringScrapeHealthClassifier->classify([
            'latency_ms' => $latencyMs,
            'sample_count' => $sampleCount,
            'collector_unavailable' => $collectorUnavailable,
        ]);

        $snapshot = MonitoringProbeSnapshot::query()->create([
            'monitoring_target_catalog_id' => $target->id,
            'reference_at' => now(),
            'scrape_status' => $classification['status']->value,
            'latency_ms' => $latencyMs,
            'sample_count' => $sampleCount,
            'failure_reason' => $failureReason,
            'metadata' => [
                'collector_type' => $target->collector_type,
                'severity' => $classification['severity']->value,
            ],
        ]);

        $target->forceFill([
            'status' => $classification['status']->value,
            'metadata' => array_merge($target->metadata ?? [], [
                'last_probe_at' => now()->toAtomString(),
                'last_probe_latency_ms' => $latencyMs,
            ]),
        ])->save();

        if ($classification['severity'] !== MonitoringSeverity::Healthy) {
            $this->monitoringConsolidationEventPublisher->publish(
                eventType: $classification['severity'] === MonitoringSeverity::Critical ? 'SCRAPE_HEALTH_CRITICO' : 'MONITORAMENTO_DEGRADADO',
                entityFingerprint: sprintf('target:%s', $target->id),
                payload: [
                    'target_id' => $target->id,
                    'target_name' => $target->target_name,
                    'flow_name' => $target->flow_name,
                    'scrape_status' => $classification['status']->value,
                    'severity' => $classification['severity']->value,
                    'latency_ms' => $latencyMs,
                    'sample_count' => $sampleCount,
                    'failure_reason' => $failureReason,
                ],
                consumers: ['platform', 'support'],
                schemaDefinition: [
                    'target_id' => 'integer',
                    'target_name' => 'string',
                    'flow_name' => 'string',
                    'scrape_status' => 'string',
                    'severity' => 'string',
                    'latency_ms' => 'integer',
                    'sample_count' => 'integer',
                    'failure_reason' => 'string|null',
                ],
            );
        }

        return $snapshot;
    }
}
