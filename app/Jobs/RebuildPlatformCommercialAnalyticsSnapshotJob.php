<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Billing\CommercialAnalyticsSnapshotService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RebuildPlatformCommercialAnalyticsSnapshotJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $days = 30,
    ) {}

    public function handle(CommercialAnalyticsSnapshotService $commercialAnalyticsSnapshotService): void
    {
        $commercialAnalyticsSnapshotService->rebuild(days: $this->days);
    }
}
