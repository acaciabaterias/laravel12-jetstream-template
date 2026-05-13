<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Support\Operations\MonitoringScrapeStatus;
use App\Support\Operations\MonitoringSeverity;

class MonitoringScrapeHealthClassifier
{
    /**
     * @param  array{latency_ms:int,sample_count:int,collector_unavailable:bool}  $metrics
     * @return array{status: MonitoringScrapeStatus, severity: MonitoringSeverity}
     */
    public function classify(array $metrics): array
    {
        if ($metrics['collector_unavailable']) {
            return [
                'status' => MonitoringScrapeStatus::Unavailable,
                'severity' => MonitoringSeverity::Critical,
            ];
        }

        $latencyWarning = (int) config('monitoring_consolidation.scrape.latency_warning_ms', 1500);
        $latencyCritical = (int) config('monitoring_consolidation.scrape.latency_critical_ms', 5000);
        $minimumSampleCount = (int) config('monitoring_consolidation.scrape.minimum_sample_count', 1);

        if ($metrics['latency_ms'] >= $latencyCritical || $metrics['sample_count'] < $minimumSampleCount) {
            return [
                'status' => MonitoringScrapeStatus::Unavailable,
                'severity' => MonitoringSeverity::Critical,
            ];
        }

        if ($metrics['latency_ms'] >= $latencyWarning) {
            return [
                'status' => MonitoringScrapeStatus::Degraded,
                'severity' => MonitoringSeverity::Warning,
            ];
        }

        return [
            'status' => MonitoringScrapeStatus::Healthy,
            'severity' => MonitoringSeverity::Healthy,
        ];
    }
}
