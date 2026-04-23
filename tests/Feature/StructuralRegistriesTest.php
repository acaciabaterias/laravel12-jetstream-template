<?php

namespace Tests\Feature;

use App\Models\Bateria;
use App\Models\Fabricante;
use App\Models\User;
use App\Models\Veiculo;
use Livewire\Livewire;
use Tests\TestCase;

class StructuralRegistriesTest extends TestCase
{
    public function test_fabricante_crud_records_audit_logs(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        $fabricante = Fabricante::create([
            'nome' => 'Moura',
            'codigo' => 'MOU',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'created',
            'table_name' => 'fabricantes',
            'record_id' => $fabricante->id,
        ]);

        $fabricante->update(['codigo' => 'MOU-01']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'updated',
            'record_id' => $fabricante->id,
        ]);

        $fabricante->delete();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'record_id' => $fabricante->id,
        ]);
    }

    public function test_structural_crud_supports_json_attributes_and_reverse_logistics(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        $fabricante = Fabricante::create(['nome' => 'VW']);

        $veiculoOrigem = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Gol',
            'atributos_dinamicos' => ['categoria' => 'Carro'],
        ]);

        $bateria = Bateria::create([
            'sku' => 'BAT-01',
            'marca' => 'Zetta',
            'peso_sucata_kg' => 12.50,
            'valor_base_sucata_kg' => 4.20,
            'atributos_dinamicos' => ['tipo' => 'AGM'],
        ]);

        $this->assertSame('Carro', $veiculoOrigem->atributos_dinamicos['categoria']);
        $this->assertSame('AGM', $bateria->atributos_dinamicos['tipo']);
        $this->assertSame('12.50', $bateria->peso_sucata_kg);
        $this->assertSame('4.20', $bateria->valor_base_sucata_kg);
    }

    public function test_cloning_applications_does_not_duplicate_existing_ones(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        $fabricante = Fabricante::create(['nome' => 'VW']);

        $veiculoOrigem = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Gol',
        ]);

        $veiculoDestino = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Fox',
        ]);

        $bateria = Bateria::create([
            'sku' => 'BAT-01',
            'marca' => 'Zetta',
        ]);

        $veiculoOrigem->baterias()->attach($bateria->id, ['observacao' => 'Aplicacao original']);

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
