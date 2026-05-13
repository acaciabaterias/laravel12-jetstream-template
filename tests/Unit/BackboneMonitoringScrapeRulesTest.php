<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Operations\MonitoringScrapeHealthClassifier;
use Tests\NonDatabaseTestCase;

class BackboneMonitoringScrapeRulesTest extends NonDatabaseTestCase
{
    public function test_it_classifies_healthy_degraded_and_unavailable_scrapes(): void
    {
        $classifier = app(MonitoringScrapeHealthClassifier::class);

        $healthy = $classifier->classify([
            'latency_ms' => 300,
            'sample_count' => 10,
            'collector_unavailable' => false,
        ]);
        $degraded = $classifier->classify([
            'latency_ms' => 2000,
            'sample_count' => 10,
            'collector_unavailable' => false,
        ]);
        $unavailable = $classifier->classify([
            'latency_ms' => 0,
            'sample_count' => 0,
            'collector_unavailable' => true,
        ]);

        $this->assertSame('healthy', $healthy['status']->value);
        $this->assertSame('healthy', $healthy['severity']->value);
        $this->assertSame('degraded', $degraded['status']->value);
        $this->assertSame('warning', $degraded['severity']->value);
        $this->assertSame('unavailable', $unavailable['status']->value);
        $this->assertSame('critical', $unavailable['severity']->value);
    }
}
