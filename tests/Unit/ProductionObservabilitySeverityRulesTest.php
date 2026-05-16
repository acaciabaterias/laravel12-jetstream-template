<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Operations\OperationalSeverityClassifier;
use Tests\TestCase;

class ProductionObservabilitySeverityRulesTest extends TestCase
{
    public function test_it_classifies_critical_and_healthy_operational_states(): void
    {
        $classifier = app(OperationalSeverityClassifier::class);

        $critical = $classifier->classify([
            'backlog_count' => 25,
            'latency_ms' => 6000,
            'failure_rate' => 0.20,
            'open_replays' => 30,
            'collector_unavailable' => false,
        ]);

        $healthy = $classifier->classify([
            'backlog_count' => 0,
            'latency_ms' => 200,
            'failure_rate' => 0.0,
            'open_replays' => 0,
            'collector_unavailable' => false,
        ]);

        $this->assertSame('critical', $critical['severity']->value);
        $this->assertSame('degraded', $critical['status']->value);
        $this->assertSame('healthy', $healthy['severity']->value);
        $this->assertSame('healthy', $healthy['status']->value);
    }
}
