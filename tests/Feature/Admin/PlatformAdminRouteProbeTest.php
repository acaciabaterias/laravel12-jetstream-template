<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class PlatformAdminRouteProbeTest extends TestCase
{
    public function test_local_environment_registers_admin_routes_under_prefix(): void
    {
        $this->assertStringContainsString('/admin/login', route('admin.login'));
        $this->assertStringContainsString('/admin/painel', route('admin.dashboard'));
    }
}
