<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Billing\ExecutiveReportingFilterNormalizer;
use Tests\NonDatabaseTestCase;

class ExecutiveReportingFilterRulesTest extends NonDatabaseTestCase
{
    public function test_it_normalizes_blank_filters_to_all_and_respects_days_bounds(): void
    {
        $normalized = app(ExecutiveReportingFilterNormalizer::class)->normalize([
            'days' => 1,
            'plan' => '',
            'channel' => '',
            'portfolio' => '',
            'recovery_status' => '',
        ]);

        $this->assertSame(7, $normalized['days']);
        $this->assertSame('all', $normalized['plan']);
        $this->assertSame('all', $normalized['channel']);
        $this->assertSame('all', $normalized['portfolio']);
        $this->assertSame('all', $normalized['recovery_status']);
    }
}
