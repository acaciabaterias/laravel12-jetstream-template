<?php

namespace Tests\Feature\Admin;

use App\Livewire\FilialSelector;
use App\Models\Cliente;
use App\Models\Filial;
use App\Models\User;
use App\Models\UsuarioPlataforma;
use App\Models\WhiteLabelConfig;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    public function test_platform_super_admin_can_view_the_admin_dashboard(): void
    {
        Filial::factory()->count(2)->create();
        User::factory()->count(3)->create(['ativo' => true]);
        WhiteLabelConfig::query()->create([
            'titulo_login' => 'ERP Central',
            'cor_primaria' => '#0f172a',
        ]);
        Cliente::factory()->create(['status' => 'active']);

        $platformAdmin = UsuarioPlataforma::factory()->superAdmin()->create();

        $response = $this
            ->actingAs($platformAdmin, 'platform')
            ->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Dashboard da Plataforma')
            ->assertSee('Tenants Ativos')
            ->assertSee('White Labels');
    }

    public function test_filial_selector_component_exists_for_web_super_admin(): void
    {
        $filiais = Filial::factory()->count(2)->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'super_admin',
        ]);

        $this->actingAs($user);

        Livewire::test(FilialSelector::class)
            ->assertSee('Contexto de Empresa')
            ->assertSee($filiais->first()->nome);
    }
}
