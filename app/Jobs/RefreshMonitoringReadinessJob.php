<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Operations\MonitoringReadinessService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshMonitoringReadinessJob implements ShouldQueue
{
    use Queueable;

    public function handle(MonitoringReadinessService $monitoringReadinessService): void
    {
        $monitoringReadinessService->refreshAll();
    }
}
