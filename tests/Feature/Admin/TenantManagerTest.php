<?php

namespace Tests\Feature\Admin;

use App\Livewire\TenantForm;
use App\Livewire\TenantManager;
use App\Models\Cliente;
use App\Models\PlanoAssinatura;
use App\Models\UsuarioPlataforma;
use Livewire\Livewire;
use Tests\TestCase;

class TenantManagerTest extends TestCase
{
    public function test_super_admin_can_view_tenant_manager(): void
    {
        $admin = UsuarioPlataforma::factory()->superAdmin()->create();
        $tenant = Cliente::factory()->create([
            'razao_social' => 'Tenant Alpha',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($admin, 'platform')
            ->get(route('admin.clientes.index'));

        $response
            ->assertOk()
            ->assertSee('Clientes SaaS e provisionamento')
            ->assertSee($tenant->razao_social)
            ->assertSeeLivewire(TenantManager::class);
    }

    public function test_support_user_can_view_tenant_manager_but_cannot_toggle_status(): void
    {
        $support = UsuarioPlataforma::factory()->create([
            'papel' => 'support',
        ]);
        $tenant = Cliente::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($support, 'platform');

        $this->get(route('admin.clientes.index'))->assertOk();

        Livewire::test(TenantManager::class)
            ->call('toggleStatus', $tenant->id)
            ->assertForbidden();
    }

    public function test_super_admin_can_create_tenant_from_form_component(): void
    {
        $admin = UsuarioPlataforma::factory()->superAdmin()->create();
        PlanoAssinatura::query()->create([
            'nome' => 'Essential',
            'slug' => 'essential',
            'preco_mensal' => 147,
        ]);

        $this->actingAs($admin, 'platform');

        Livewire::test(TenantForm::class)
            ->set('cnpj', '12.345.678/0001-90')
            ->set('razaoSocial', 'Tenant Beta LTDA')
            ->set('nomeFantasia', 'Tenant Beta')
            ->set('emailContato', 'beta@tenant.test')
            ->set('telefone', '11999999999')
            ->set('subdominio', 'tenant-beta')
            ->set('plano', 'essential')
            ->set('status', 'trial')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clientes', [
            'razao_social' => 'Tenant Beta LTDA',
            'subdominio' => 'tenant-beta',
            'plano' => 'essential',
        ]);
    }

    public function test_super_admin_can_update_existing_tenant(): void
    {
        $admin = UsuarioPlataforma::factory()->superAdmin()->create();
        $tenant = Cliente::factory()->create([
            'razao_social' => 'Tenant Atual',
            'status' => 'trial',
        ]);

        $this->actingAs($admin, 'platform');

        Livewire::test(TenantForm::class, ['tenant' => $tenant])
            ->set('razaoSocial', 'Tenant Atualizado')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clientes', [
            'id' => $tenant->id,
            'razao_social' => 'Tenant Atualizado',
            'status' => 'active',
        ]);
    }
}
