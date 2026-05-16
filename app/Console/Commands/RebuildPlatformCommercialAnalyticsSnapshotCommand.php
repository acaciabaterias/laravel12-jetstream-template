<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\RebuildPlatformCommercialAnalyticsSnapshotJob;
use Illuminate\Console\Command;

class RebuildPlatformCommercialAnalyticsSnapshotCommand extends Command
{
    protected $signature = 'analytics:rebuild-platform-commercial-snapshot {--days=30 : Janela em dias do snapshot}';

    protected $description = 'Reconstrói o snapshot central de analytics comercial da plataforma';

    public function handle(): int
    {
        RebuildPlatformCommercialAnalyticsSnapshotJob::dispatch((int) $this->option('days'));

        $this->info('Job de rebuild do analytics comercial despachado.');

        return self::SUCCESS;
    }
}
