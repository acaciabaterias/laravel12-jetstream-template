<?php

namespace Tests\Feature;

use App\Livewire\UserManager;
use App\Models\Filial;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    public function test_dono_can_create_vendedor_and_write_audit_log(): void
    {
        $filial = Filial::factory()->create();
        $owner = User::factory()->withPersonalTeam()->create([
            'papel' => 'dono',
            'filial_id' => $filial->id,
        ]);

        $this->actingAs($owner);

        Livewire::test(UserManager::class)
            ->set('name', 'Joao Vendedor')
            ->set('email', 'joao@example.com')
            ->set('password', 'password123')
            ->set('papel', 'vendedor')
            ->set('ativo', true)
            ->call('createUser')
            ->assertHasNoErrors();

        $createdUser = User::query()->where('email', 'joao@example.com')->firstOrFail();

        $this->assertSame($filial->id, $createdUser->filial_id);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $owner->id,
            'action' => 'created',
            'table_name' => 'users',
            'record_id' => $createdUser->id,
        ]);
    }

    public function test_vendedor_cannot_create_users(): void
    {
        $filial = Filial::factory()->create();
        $seller = User::factory()->withPersonalTeam()->create([
            'papel' => 'vendedor',
            'filial_id' => $filial->id,
        ]);

        $this->assertFalse(Gate::forUser($seller)->check('create', User::class));
    }

    public function test_gestor_can_toggle_active_status_and_log_audit(): void
    {
        $filial = Filial::factory()->create();
        $manager = User::factory()->withPersonalTeam()->create([
            'papel' => 'gestor',
            'filial_id' => $filial->id,
        ]);
        $target = User::factory()->create([
            'papel' => 'vendedor',
            'filial_id' => $filial->id,
            'ativo' => true,
        ]);

        $this->actingAs($manager);

        Livewire::test(UserManager::class)
            ->call('toggleActive', $target->id);

        $this->assertFalse($target->fresh()->ativo);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $manager->id,
            'action' => 'deactivated',
            'table_name' => 'users',
            'record_id' => $target->id,
        ]);
    }

    public function test_user_manager_lists_only_users_from_current_filial(): void
    {
        $filialA = Filial::factory()->create();
        $filialB = Filial::factory()->create();

        $manager = User::factory()->withPersonalTeam()->create([
            'papel' => 'gestor',
            'filial_id' => $filialA->id,
        ]);

        User::factory()->create([
            'name' => 'Usuario A',
            'filial_id' => $filialA->id,
        ]);

        User::factory()->create([
            'name' => 'Usuario B',
            'filial_id' => $filialB->id,
        ]);

        $this->actingAs($manager);

        Livewire::test(UserManager::class)
            ->assertSee('Usuario A')
            ->assertDontSee('Usuario B');
    }
}
