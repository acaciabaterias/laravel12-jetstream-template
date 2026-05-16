<?php

namespace Tests\Feature\Admin;

use App\Models\UsuarioPlataforma;
use Tests\TestCase;

class PlatformAdminAuthenticationTest extends TestCase
{
    public function test_guest_can_view_the_platform_admin_login_page(): void
    {
        $response = $this->get(route('admin.login'));

        $response
            ->assertOk()
            ->assertSee('Login administrativo')
            ->assertSee('BateriaExpert Admin');
    }

    public function test_platform_admin_can_log_in_with_valid_credentials(): void
    {
        $admin = UsuarioPlataforma::factory()->superAdmin()->create([
            'email' => 'admin@plataforma.test',
            'password' => 'password',
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'platform');
    }

    public function test_platform_admin_login_rejects_invalid_credentials(): void
    {
        UsuarioPlataforma::factory()->superAdmin()->create([
            'email' => 'admin@plataforma.test',
            'password' => 'password',
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => 'admin@plataforma.test',
            'password' => 'senha-errada',
        ]);

        $response
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest('platform');
    }

    public function test_guest_is_redirected_to_admin_login_when_accessing_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }
}
