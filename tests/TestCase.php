<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function afterRefreshingDatabase()
    {
        if (($this->app['config']->get('database.connections.tenant.driver') ?? null) !== 'pgsql') {
            return;
        }

        $this->artisan('migrate:fresh', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant/2026_05_06_204458_create_integration_backbone_tables.php',
            '--realpath' => false,
        ])->assertExitCode(0);
    }
}
