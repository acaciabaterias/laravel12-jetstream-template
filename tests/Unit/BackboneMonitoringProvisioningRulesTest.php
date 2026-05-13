<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\DashboardProvisioningRecord;
use App\Services\Operations\MonitoringProvisioningService;
use InvalidArgumentException;
use Tests\NonDatabaseTestCase;

class BackboneMonitoringProvisioningRulesTest extends NonDatabaseTestCase
{
    public function test_it_blocks_validation_and_rollback_for_pending_records(): void
    {
        $service = app(MonitoringProvisioningService::class);
        $pendingRecord = DashboardProvisioningRecord::factory()->make([
            'status' => 'pending',
            'applied_at' => null,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $service->markValidated($pendingRecord);
    }

    public function test_it_requires_applied_record_for_rollback(): void
    {
        $service = app(MonitoringProvisioningService::class);
        $pendingRecord = DashboardProvisioningRecord::factory()->make([
            'status' => 'pending',
            'applied_at' => null,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $service->rollback($pendingRecord, ['rollback_version' => 'v1.0.0']);
    }
}
