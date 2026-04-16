<?php

namespace Tests\Feature;

use App\Models\Filial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class FilialSelectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_selector_component_is_rendered(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        Filial::factory()->create(['nome' => 'Filial Matriz']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('Filial Matriz');
    }

    public function test_can_switch_filial(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $filial1 = Filial::factory()->create(['nome' => 'Filial 1']);
        $filial2 = Filial::factory()->create(['nome' => 'Filial 2']);

        $this->actingAs($user);

        Volt::test('filial-selector')
            ->assertSee('Filial 1')
            ->call('switchFilial', $filial2->id)
            ->assertDispatched('filial-switched');

        $this->assertEquals($filial2->id, session('filial_id'));
    }
}
