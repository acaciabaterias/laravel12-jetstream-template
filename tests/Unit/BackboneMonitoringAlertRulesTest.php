<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AlertRuleDefinition;
use App\Services\Operations\MonitoringAlertRuleEvaluator;
use Tests\NonDatabaseTestCase;

class BackboneMonitoringAlertRulesTest extends NonDatabaseTestCase
{
    public function test_it_maps_threshold_operators_for_latency_and_collector_unavailability(): void
    {
        $evaluator = app(MonitoringAlertRuleEvaluator::class);
        $latencyRule = AlertRuleDefinition::factory()->make([
            'severity' => 'critical',
            'metadata' => [
                'metric' => 'latency_ms',
                'operator' => 'gte',
                'threshold' => 5000,
            ],
        ]);
        $collectorRule = AlertRuleDefinition::factory()->make([
            'severity' => 'critical',
            'metadata' => [
                'metric' => 'collector_unavailable',
                'operator' => 'eq',
                'threshold' => true,
            ],
        ]);

        $latencyTriggered = $evaluator->evaluate($latencyRule, [
            'latency_ms' => 5100,
            'sample_count' => 12,
            'collector_unavailable' => false,
            'scrape_status' => 'healthy',
        ]);
        $latencyClear = $evaluator->evaluate($latencyRule, [
            'latency_ms' => 2400,
            'sample_count' => 12,
            'collector_unavailable' => false,
            'scrape_status' => 'healthy',
        ]);
        $collectorTriggered = $evaluator->evaluate($collectorRule, [
            'latency_ms' => 0,
            'sample_count' => 0,
            'collector_unavailable' => true,
            'scrape_status' => 'unavailable',
        ]);

        $this->assertTrue($latencyTriggered['triggered']);
        $this->assertSame(5100, $latencyTriggered['actual']);
        $this->assertFalse($latencyClear['triggered']);
        $this->assertTrue($collectorTriggered['triggered']);
        $this->assertSame('collector_unavailable eq true', $collectorTriggered['reason']);
    }
}
