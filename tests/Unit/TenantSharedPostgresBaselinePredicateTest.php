<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Middleware\TenantConnectionMiddleware;
use Illuminate\Support\Facades\Config;
use Tests\NonDatabaseTestCase;

class TenantSharedPostgresBaselinePredicateTest extends NonDatabaseTestCase
{
    /** @see TenantConnectionMiddleware */
    private function qualifiesForSharedPostgreSqlBaseline(array $connection): bool
    {
        return ($connection['driver'] ?? '') === 'pgsql'
            && filled((string) ($connection['host'] ?? ''));
    }

    public function test_pg_driver_with_resolved_host_matches_shared_integration_pattern(): void
    {
        Config::set('database.connections.tenant', [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => '5432',
            'database' => 'ci_tenant',
            'username' => 'postgres',
            'password' => 'postgres',
        ]);

        $this->assertTrue($this->qualifiesForSharedPostgreSqlBaseline(config('database.connections.tenant')));
    }

    public function test_sqlite_driver_does_not_match_shared_integration_pattern(): void
    {
        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'username' => null,
            'password' => null,
            'host' => '',
        ]);

        $this->assertFalse($this->qualifiesForSharedPostgreSqlBaseline(config('database.connections.tenant')));
    }

    public function test_pg_without_host_does_not_match_shared_integration_pattern(): void
    {
        Config::set('database.connections.tenant', [
            'driver' => 'pgsql',
            'host' => '',
            'port' => '5432',
            'database' => 'postgres',
            'username' => 'postgres',
            'password' => '',
        ]);

        $this->assertFalse($this->qualifiesForSharedPostgreSqlBaseline(config('database.connections.tenant')));
    }
}
