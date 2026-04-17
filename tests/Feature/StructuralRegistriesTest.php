<?php

namespace Tests\Feature;

use App\Models\Bateria;
use App\Models\Fabricante;
use App\Models\Filial;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StructuralRegistriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_multitenant_scope_isolates_records_by_filial()
    {
        $filialA = Filial::factory()->create();
        $filialB = Filial::factory()->create();

        // Bypass scope by not authenticating to create data
        Fabricante::create(['nome' => 'Fab A', 'filial_id' => $filialA->id]);
        Fabricante::create(['nome' => 'Fab B', 'filial_id' => $filialB->id]);

        $userA = User::factory()->create(['filial_id' => $filialA->id]);
        $this->actingAs($userA);

        $fabricantes = Fabricante::all();
        $this->assertCount(1, $fabricantes);
        $this->assertEquals('Fab A', $fabricantes->first()->nome);
    }

    public function test_audit_logs_are_recorded_on_crud_operations()
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->create(['filial_id' => $filial->id]);
        $this->actingAs($user);

        $bateria = Bateria::create([
            'sku' => 'BAT123',
            'marca' => 'Moura',
            'preco_venda' => 150.00,
            'filial_id' => $filial->id
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'created',
            'table_name' => 'baterias',
            'record_id' => $bateria->id,
        ]);

        $bateria->update(['preco_venda' => 160.00]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'updated',
            'record_id' => $bateria->id,
        ]);
        
        $bateria->delete();
        
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'record_id' => $bateria->id,
        ]);
    }

    public function test_cloning_applications_does_not_duplicate_existing_ones()
    {
        $filial = Filial::factory()->create();
        
        // Scope bypass for setup or we can authenticate right away
        $user = User::factory()->create(['filial_id' => $filial->id]);
        $this->actingAs($user);

        $fabricante = Fabricante::create(['nome' => 'VW', 'filial_id' => $filial->id]);

        $veiculoOrigem = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Gol',
            'filial_id' => $filial->id
        ]);

        $veiculoDestino = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Fox',
            'filial_id' => $filial->id
        ]);

        $bateria = Bateria::create([
            'sku' => 'BAT-01',
            'marca' => 'Zetta',
            'filial_id' => $filial->id
        ]);

        $veiculoOrigem->baterias()->attach($bateria->id, ['filial_id' => $filial->id]);

        Livewire::test(\App\Livewire\AplicacaoCloner::class)
            ->call('openCloner', $veiculoDestino->id)
            ->set('origemVeiculoId', $veiculoOrigem->id)
            ->call('cloneAplicacoes');

        $this->assertCount(1, $veiculoDestino->fresh()->baterias);
        
        Livewire::test(\App\Livewire\AplicacaoCloner::class)
            ->call('openCloner', $veiculoDestino->id)
            ->set('origemVeiculoId', $veiculoOrigem->id)
            ->call('cloneAplicacoes');

        $this->assertCount(1, $veiculoDestino->fresh()->baterias);
    }
}
