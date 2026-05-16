<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Operations\OperationalHealthSnapshotService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RebuildOperationalHealthSnapshotJob implements ShouldQueue
{
    use Queueable;

    public function handle(OperationalHealthSnapshotService $operationalHealthSnapshotService): void
    {
        $operationalHealthSnapshotService->rebuild();
    }
}
