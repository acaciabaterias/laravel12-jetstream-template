<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\PerformanceBottleneckRecord;
use Tests\NonDatabaseTestCase;

class CriticalLoadBottleneckRulesTest extends NonDatabaseTestCase
{
    public function test_it_maps_bottleneck_category_and_impact_level_casts(): void
    {
        $bottleneck = new PerformanceBottleneckRecord([
            'benchmark_execution_record_id' => 1,
            'flow_name' => 'integration_backbone',
            'category' => 'database',
            'component_name' => 'evento_outboxes_lookup',
            'summary' => 'Consulta sem indice degrada durante picos.',
            'impact_level' => 'critical',
            'evidence_payload' => ['samples' => [1, 2, 3]],
            'metadata' => ['source' => 'unit-test'],
        ]);

        $this->assertSame('database', $bottleneck->category->value);
        $this->assertSame('critical', $bottleneck->impact_level->value);
    }
}
