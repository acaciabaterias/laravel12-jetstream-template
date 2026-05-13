<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Operations\MonitoringReadinessService;
use Illuminate\Console\Command;

class RefreshMonitoringReadinessCommand extends Command
{
    protected $signature = 'monitoring:refresh-readiness';

    protected $description = 'Refresh monitoring readiness snapshots for central targets';

    public function handle(MonitoringReadinessService $monitoringReadinessService): int
    {
        $snapshots = $monitoringReadinessService->refreshAll();

        $this->info(sprintf('%d monitoring targets refreshed.', $snapshots->count()));

        return self::SUCCESS;
    }
}
