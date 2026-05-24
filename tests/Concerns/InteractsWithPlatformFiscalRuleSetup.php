<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Facades\Artisan;

trait InteractsWithPlatformFiscalRuleSetup
{
    protected function runPlatformFiscalRuleMigrations(bool $includeBackbone = false): void
    {
        $migrations = [
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_05_18_190000_create_central_platform_fiscal_rule_tables.php',
            'database/migrations/central/2026_05_23_222046_add_tax_profile_support_to_platform_fiscal_rules_table.php',
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
