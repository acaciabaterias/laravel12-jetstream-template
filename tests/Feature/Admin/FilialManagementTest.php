<?php

namespace Tests\Feature\Admin;

use App\Models\Filial;
use App\Models\User;
use App\Models\UsuarioPlataforma;
use Tests\TestCase;

class FilialManagementTest extends TestCase
{
    public function test_platform_super_admin_can_list_filiais(): void
    {
        $platformAdmin = UsuarioPlataforma::factory()->superAdmin()->create();
        $filial = Filial::factory()->create([
            'nome' => 'Matriz Centro',
        ]);

        $response = $this
            ->actingAs($platformAdmin, 'platform')
            ->get(route('admin.filiais.index'));

        $response
            ->assertOk()
            ->assertSee('Filiais')
            ->assertSee($filial->nome);
    }

    public function test_non_super_admin_platform_user_cannot_manage_filiais(): void
    {
        $supportUser = UsuarioPlataforma::factory()->create([
            'papel' => 'support',
        ]);

        $response = $this
            ->actingAs($supportUser, 'platform')
            ->get(route('admin.filiais.index'));

        $response->assertForbidden();
    }

    public function test_platform_super_admin_can_create_filial(): void
    {
        $platformAdmin = UsuarioPlataforma::factory()->superAdmin()->create();

        $response = $this
            ->actingAs($platformAdmin, 'platform')
            ->post(route('admin.filiais.store'), [
                'nome' => 'Filial Zona Sul',
                'cnpj' => '12.345.678/0001-90',
            ]);

        $response
            ->assertRedirect(route('admin.filiais.index'))
            ->assertSessionHas('status', 'Filial criada com sucesso.');

        $this->assertDatabaseHas('filiais', [
            'nome' => 'Filial Zona Sul',
            'cnpj' => '12.345.678/0001-90',
        ]);
    }

    public function test_platform_super_admin_can_update_filial(): void
    {
        $platformAdmin = UsuarioPlataforma::factory()->superAdmin()->create();
        $filial = Filial::factory()->create();

        $response = $this
            ->actingAs($platformAdmin, 'platform')
            ->put(route('admin.filiais.update', $filial), [
                'nome' => 'Filial Atualizada',
                'cnpj' => $filial->cnpj,
            ]);

        $response
            ->assertRedirect(route('admin.filiais.index'))
            ->assertSessionHas('status', 'Filial atualizada com sucesso.');

        $this->assertDatabaseHas('filiais', [
            'id' => $filial->id,
            'nome' => 'Filial Atualizada',
        ]);
    }

    public function test_platform_super_admin_cannot_delete_filial_with_users(): void
    {
        $platformAdmin = UsuarioPlataforma::factory()->superAdmin()->create();
        $filial = Filial::factory()->create();

        User::factory()->create([
            'filial_id' => $filial->id,
        ]);

        $response = $this
            ->actingAs($platformAdmin, 'platform')
            ->delete(route('admin.filiais.destroy', $filial));

        $response
            ->assertRedirect(route('admin.filiais.index'))
            ->assertSessionHas('error', 'Não é possível excluir uma filial com usuários vinculados.');

        $this->assertDatabaseHas('filiais', [
            'id' => $filial->id,
        ]);
    }
}
