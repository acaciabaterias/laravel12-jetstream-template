<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ExecutiveAnalyticsSnapshot;
use App\Support\Billing\ExecutiveAnalyticsSnapshotStatus;
use Tests\NonDatabaseTestCase;

class ExecutiveReportingSnapshotSerializationTest extends NonDatabaseTestCase
{
    public function test_it_casts_snapshot_payloads_and_serializes_their_exportable_shape(): void
    {
        $snapshot = new ExecutiveAnalyticsSnapshot([
            'reference_date' => '2026-05-30',
            'period_start' => '2026-05-01',
            'period_end' => '2026-05-30',
            'filter_payload' => [
                'days' => 30,
                'plan' => 'enterprise',
                'channel' => 'pix',
                'portfolio' => 'blocked',
                'recovery_status' => 'open',
            ],
            'kpi_payload' => [
                'mrr' => 1199.9,
                'active_subscriptions' => 4,
            ],
            'drilldown_payload' => [
                'plans' => [
                    ['label' => 'Plano Enterprise', 'subscriptions' => 2],
                ],
            ],
            'snapshot_status' => 'ready',
        ]);

        $this->assertSame(ExecutiveAnalyticsSnapshotStatus::Ready, $snapshot->snapshot_status);
        $this->assertSame('enterprise', $snapshot->filter_payload['plan']);
        $this->assertSame(4, $snapshot->kpi_payload['active_subscriptions']);
        $this->assertSame('Plano Enterprise', $snapshot->drilldown_payload['plans'][0]['label']);

        $serialized = $snapshot->toArray();

        $this->assertSame('2026-05-30T00:00:00.000000Z', $serialized['reference_date']);
        $this->assertSame('2026-05-01T00:00:00.000000Z', $serialized['period_start']);
        $this->assertSame('2026-05-30T00:00:00.000000Z', $serialized['period_end']);
        $this->assertSame('ready', $serialized['snapshot_status']);
        $this->assertSame('pix', $serialized['filter_payload']['channel']);
        $this->assertSame(1199.9, $serialized['kpi_payload']['mrr']);
    }
}
