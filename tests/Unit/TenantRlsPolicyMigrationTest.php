<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\NonDatabaseTestCase;

class TenantRlsPolicyMigrationTest extends NonDatabaseTestCase
{
    public function test_tenant_rls_sql_bootstraps_supabase_roles_when_missing(): void
    {
        $sql = file_get_contents(database_path('schema/tenant_rls_policies.sql'));

        $this->assertIsString($sql);
        $this->assertStringContainsString("rolname = 'authenticated'", $sql);
        $this->assertStringContainsString('create role authenticated nologin', $sql);
        $this->assertStringContainsString("rolname = 'service_role'", $sql);
        $this->assertStringContainsString('create role service_role nologin', $sql);
    }
}
