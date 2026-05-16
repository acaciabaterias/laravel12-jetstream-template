<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\RebuildOperationalHealthSnapshotJob;
use Illuminate\Console\Command;

class RebuildOperationalHealthSnapshotCommand extends Command
{
    protected $signature = 'operations:rebuild-health-snapshot';

    protected $description = 'Reconstrói o snapshot central de saude operacional';

    public function handle(): int
    {
        RebuildOperationalHealthSnapshotJob::dispatch();

        $this->info('Job de saude operacional despachado.');

        return self::SUCCESS;
    }
}
