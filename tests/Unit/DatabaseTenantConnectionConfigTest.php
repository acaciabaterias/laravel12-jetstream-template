<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\NonDatabaseTestCase;

class DatabaseTenantConnectionConfigTest extends NonDatabaseTestCase
{
    public function test_tenant_connection_includes_host_and_password_entries(): void
    {
        $tenant = config('database.connections.tenant');

        $this->assertIsArray($tenant);
        foreach (['driver', 'host', 'port', 'database', 'username', 'password'] as $key) {
            $this->assertArrayHasKey($key, $tenant, "tenant connection deve definir {$key}");
        }
    }
}
