<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminLoginPageSmokeTest extends TestCase
{
    public function test_admin_login_page_loads_on_local_prefix(): void
    {
        $response = $this->get('/admin/login');

        $response
            ->assertOk()
            ->assertSee('Login administrativo')
            ->assertSee('Entrar no painel admin');
    }
}
