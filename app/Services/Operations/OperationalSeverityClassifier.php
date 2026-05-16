<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Support\Operations\CollectorHealthStatus;
use App\Support\Operations\OperationalSeverity;

class OperationalSeverityClassifier
{
    /**
     * @param array{backlog_count:int,latency_ms:int,failure_rate:float,open_replays:int,collector_unavailable:bool} $metrics
     * @return array{status: CollectorHealthStatus, severity: OperationalSeverity}
     */
    public function classify(array $metrics): array
    {
        if ($metrics['collector_unavailable']) {
            return [
                'status' => CollectorHealthStatus::Unavailable,
                'severity' => OperationalSeverity::Critical,
            ];
        }

        $backlogWarning = (int) config('production_observability.thresholds.backlog_warning', 5);
        $backlogCritical = (int) config('production_observability.thresholds.backlog_critical', 20);
        $latencyWarning = (int) config('production_observability.thresholds.latency_warning_ms', 1500);
        $latencyCritical = (int) config('production_observability.thresholds.latency_critical_ms', 5000);
        $failureRateWarning = (float) config('production_observability.thresholds.failure_rate_warning', 0.05);
        $failureRateCritical = (float) config('production_observability.thresholds.failure_rate_critical', 0.15);

        $isCritical = $metrics['backlog_count'] >= $backlogCritical
            || $metrics['latency_ms'] >= $latencyCritical
            || $metrics['failure_rate'] >= $failureRateCritical
            || $metrics['open_replays'] >= $backlogCritical;

        if ($isCritical) {
            return [
                'status' => CollectorHealthStatus::Degraded,
                'severity' => OperationalSeverity::Critical,
            ];
        }

        $isWarning = $metrics['backlog_count'] >= $backlogWarning
            || $metrics['latency_ms'] >= $latencyWarning
            || $metrics['failure_rate'] >= $failureRateWarning
            || $metrics['open_replays'] >= $backlogWarning;

        if ($isWarning) {
            return [
                'status' => CollectorHealthStatus::Degraded,
                'severity' => OperationalSeverity::Warning,
            ];
        }

        return [
            'status' => CollectorHealthStatus::Healthy,
            'severity' => OperationalSeverity::Healthy,
        ];
    }
}
