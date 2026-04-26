<?php

namespace Tests\Feature;

use App\Models\Bateria;
use App\Models\Fabricante;
use App\Models\User;
use App\Models\Veiculo;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileSyncApiTest extends TestCase
{
    public function test_mobile_sync_returns_offline_catalog_payload(): void
    {
        Sanctum::actingAs(User::factory()->create([
            'papel' => 'super_admin',
        ]));

        $fabricante = Fabricante::create([
            'nome' => 'Moura',
            'codigo' => 'MOU',
        ]);

        $veiculo = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Gol',
            'motorizacao' => '1.0',
            'ano_inicio' => 2018,
            'ano_fim' => 2022,
        ]);

        $bateria = Bateria::create([
            'sku' => 'BAT-60',
            'marca' => 'Zetta',
            'tecnologia' => 'AGM',
            'polo' => 'D',
        ]);

        $veiculo->baterias()->attach($bateria->id);

        $response = $this->getJson('/api/sync/mobile');

        $response
            ->assertOk()
            ->assertJsonPath('fabricantes.0.nome', 'Moura')
            ->assertJsonPath('veiculos.0.modelo', 'Gol')
            ->assertJsonPath('veiculos.0.baterias.0.sku', 'BAT-60')
            ->assertJsonStructure([
                'fabricantes' => [
                    ['id', 'nome'],
                ],
                'veiculos' => [
                    [
                        'id',
                        'fabricante_id',
                        'modelo',
                        'motorizacao',
                        'ano_inicio',
                        'ano_fim',
                        'baterias' => [
                            ['id', 'sku', 'marca', 'tecnologia', 'polo'],
                        ],
                    ],
                ],
                'timestamp',
            ]);
    }
}
