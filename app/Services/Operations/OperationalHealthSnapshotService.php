<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\CasoRecuperacaoReceita;
use App\Models\ExcecaoConciliacaoSaaS;
use App\Models\OperationalAlertSnapshot;
use App\Models\SnapshotAnalyticsComercial;
use App\Services\Integration\IntegrationMetrics;
use App\Services\Integration\IntegrationStorageManager;
use App\Support\Billing\PaymentExceptionStatus;
use App\Support\Integration\IntegrationFlowStatus;
use App\Support\Operations\OperationalSeverity;
use Illuminate\Support\Facades\Schema;

class OperationalHealthSnapshotService
{
    public function __construct(
        private readonly IntegrationMetrics $integrationMetrics,
        private readonly IntegrationStorageManager $integrationStorageManager,
        private readonly OperationalSeverityClassifier $operationalSeverityClassifier,
        private readonly ProductionObservabilityEventPublisher $productionObservabilityEventPublisher,
    ) {}

    /**
     * @return array<int, OperationalAlertSnapshot>
     */
    public function rebuild(): array
    {
        $snapshots = [
            $this->captureIntegrationBackbone(),
            $this->capturePlatformPayments(),
            $this->capturePlatformRecovery(),
            $this->capturePlatformAnalytics(),
        ];

        foreach ($snapshots as $snapshot) {
            if ($snapshot->severity !== OperationalSeverity::Healthy) {
                $this->publishSnapshotEvents($snapshot);
            }
        }

        return $snapshots;
    }

    private function captureIntegrationBackbone(): OperationalAlertSnapshot
    {
        $collectorUnavailable = ! $this->hasCentralBackboneTables();
        $metrics = $collectorUnavailable
            ? []
            : $this->integrationStorageManager->using('central', fn (): array => $this->integrationMetrics->snapshot());

        $backlogCount = (int) (($metrics['outboxes'][IntegrationFlowStatus::Pending->value] ?? 0)
            + ($metrics['outboxes'][IntegrationFlowStatus::DeadLetter->value] ?? 0));
        $openReplays = (int) (($metrics['deliveries']['outbound'][IntegrationFlowStatus::DeadLetter->value] ?? 0));
        $latencyMs = (int) round(collect($metrics['latency']['outbound'] ?? [])->avg() ?? 0);
        $failureRate = $this->calculateFailureRate($metrics['deliveries'] ?? []);

        return $this->persistSnapshot('integration_backbone', $backlogCount, $latencyMs, $failureRate, $openReplays, $collectorUnavailable, [
            'source' => 'integration_metrics',
            'snapshot' => $metrics,
        ]);
    }

    private function capturePlatformPayments(): OperationalAlertSnapshot
    {
        $collectorUnavailable = ! Schema::connection('central')->hasTable('excecoes_conciliacao_saas');
        $backlogCount = 0;
        $latencyMs = 0;
        $failureRate = 0.0;
        $openReplays = 0;

        if (! $collectorUnavailable) {
            $backlogCount = ExcecaoConciliacaoSaaS::query()
                ->where('status', PaymentExceptionStatus::Open->value)
                ->count();

            $failed = ExcecaoConciliacaoSaaS::query()
                ->where('status', PaymentExceptionStatus::Open->value)
                ->count();
            $total = ExcecaoConciliacaoSaaS::query()->count();
            $failureRate = $total > 0 ? round($failed / $total, 4) : 0.0;
        }

        return $this->persistSnapshot('platform_payments', $backlogCount, $latencyMs, $failureRate, $openReplays, $collectorUnavailable, [
            'source' => 'payment_exceptions',
        ]);
    }

    private function capturePlatformRecovery(): OperationalAlertSnapshot
    {
        $collectorUnavailable = ! Schema::connection('central')->hasTable('casos_recuperacao_receita');
        $backlogCount = 0;
        $latencyMs = 0;
        $failureRate = 0.0;
        $openReplays = 0;

        if (! $collectorUnavailable) {
            $backlogCount = CasoRecuperacaoReceita::query()
                ->whereIn('status', ['open', 'escalated'])
                ->count();

            $stalledCases = CasoRecuperacaoReceita::query()
                ->whereIn('status', ['open', 'escalated'])
                ->whereNotNull('last_action_at')
                ->where('last_action_at', '<=', now()->subDays(5))
                ->count();
            $total = CasoRecuperacaoReceita::query()->count();
            $failureRate = $total > 0 ? round($stalledCases / $total, 4) : 0.0;
        }

        return $this->persistSnapshot('platform_recovery', $backlogCount, $latencyMs, $failureRate, $openReplays, $collectorUnavailable, [
            'source' => 'recovery_cases',
        ]);
    }

    private function capturePlatformAnalytics(): OperationalAlertSnapshot
    {
        $collectorUnavailable = ! Schema::connection('central')->hasTable('snapshots_analytics_comercial');
        $backlogCount = 0;
        $latencyMs = 0;
        $failureRate = 0.0;
        $openReplays = 0;
        $metadata = ['source' => 'commercial_analytics'];

        if (! $collectorUnavailable) {
            $latestSnapshot = SnapshotAnalyticsComercial::query()->latest('reference_date')->first();
            $staleHours = (int) config('production_observability.thresholds.stale_analytics_hours', 24);

            if ($latestSnapshot === null) {
                $backlogCount = 1;
                $failureRate = 1.0;
                $metadata['reason'] = 'missing_snapshot';
            } else {
                $hoursSinceReference = now()->diffInHours($latestSnapshot->reference_date);
                $backlogCount = $hoursSinceReference >= $staleHours ? 1 : 0;
                $failureRate = $hoursSinceReference >= $staleHours ? 1.0 : 0.0;
                $metadata['latest_snapshot_id'] = $latestSnapshot->id;
                $metadata['hours_since_reference'] = $hoursSinceReference;
            }
        }

        return $this->persistSnapshot('platform_analytics', $backlogCount, $latencyMs, $failureRate, $openReplays, $collectorUnavailable, $metadata);
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function persistSnapshot(
        string $flowName,
        int $backlogCount,
        int $latencyMs,
        float $failureRate,
        int $openReplays,
        bool $collectorUnavailable,
        array $metadata = [],
    ): OperationalAlertSnapshot {
        $classification = $this->operationalSeverityClassifier->classify([
            'backlog_count' => $backlogCount,
            'latency_ms' => $latencyMs,
            'failure_rate' => $failureRate,
            'open_replays' => $openReplays,
            'collector_unavailable' => $collectorUnavailable,
        ]);

        return OperationalAlertSnapshot::query()->create([
            'reference_at' => now(),
            'flow_name' => $flowName,
            'status' => $classification['status']->value,
            'severity' => $classification['severity']->value,
            'backlog_count' => $backlogCount,
            'latency_ms' => $latencyMs > 0 ? $latencyMs : null,
            'failure_rate' => $failureRate,
            'open_replays' => $openReplays,
            'metadata' => $metadata,
        ]);
    }

    /**
     * @param array<string, array<string, int>> $deliveries
     */
    private function calculateFailureRate(array $deliveries): float
    {
        $all = 0;
        $failed = 0;

        foreach ($deliveries as $statuses) {
            foreach ($statuses as $status => $count) {
                $all += (int) $count;
                if (in_array($status, [IntegrationFlowStatus::Failed->value, IntegrationFlowStatus::DeadLetter->value], true)) {
                    $failed += (int) $count;
                }
            }
        }

        return $all > 0 ? round($failed / $all, 4) : 0.0;
    }

    private function publishSnapshotEvents(OperationalAlertSnapshot $snapshot): void
    {
        $eventType = $snapshot->severity === OperationalSeverity::Critical
            ? 'SERVICO_DEGRADADO_DETECTADO'
            : 'INCIDENTE_OPERACIONAL_ABERTO';

        $this->productionObservabilityEventPublisher->publish(
            eventType: $eventType,
            operationalAlertSnapshot: $snapshot,
            payload: [
                'snapshot_id' => $snapshot->id,
                'flow_name' => $snapshot->flow_name,
                'status' => $snapshot->status->value,
                'severity' => $snapshot->severity->value,
                'backlog_count' => $snapshot->backlog_count,
                'failure_rate' => (float) $snapshot->failure_rate,
                'open_replays' => $snapshot->open_replays,
            ],
            consumers: ['platform', 'support'],
            schemaDefinition: [
                'snapshot_id' => 'integer',
                'flow_name' => 'string',
                'status' => 'string',
                'severity' => 'string',
                'backlog_count' => 'integer',
                'failure_rate' => 'float',
                'open_replays' => 'integer',
            ],
        );
    }

    private function hasCentralBackboneTables(): bool
    {
        return Schema::connection('central')->hasTable('evento_outboxes')
            && Schema::connection('central')->hasTable('entregas_integracao')
            && Schema::connection('central')->hasTable('contratos_evento');
    }
}
