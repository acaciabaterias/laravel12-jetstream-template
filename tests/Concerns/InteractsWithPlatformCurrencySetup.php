<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Facades\Artisan;

trait InteractsWithPlatformCurrencySetup
{
    protected function runPlatformCurrencyMigrations(bool $includeBackbone = false): void
    {
        $migrations = [
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_05_18_180000_create_central_platform_currency_tables.php',
        ];

        if ($includeBackbone) {
            $migrations[] = 'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php';
        }

        foreach ($migrations as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }
}
