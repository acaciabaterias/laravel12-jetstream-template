<?php

namespace Tests\Feature;

use App\Services\EtaService;
use App\Services\TspService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeocodingMsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste do algoritmo TSP (2-opt).
     */
    public function test_tsp_optimization_improves_route(): void
    {
        $tsp = new TspService;

        $nodes = [
            0 => ['lat' => -23.5505, 'lng' => -46.6333],
            1 => ['lat' => -23.5614, 'lng' => -46.6558],
            2 => ['lat' => -23.5489, 'lng' => -46.6388],
            3 => ['lat' => -23.5667, 'lng' => -46.6667],
        ];

        $optimized = $tsp->optimize($nodes);

        $this->assertCount(4, $optimized);
        $this->assertEquals(0, $optimized[0]);
    }

    /**
     * Teste de cálculo de ETA por distância.
     */
    public function test_eta_calculation_by_distance(): void
    {
        $eta = new EtaService;

        $this->assertEquals(60, $eta->estimateByDistance(40, true));

        $this->assertEquals(60, $eta->estimateByDistance(80, false));
    }

    /**
     * Teste do motor de Roteirização completo via Endpoint.
     */
    public function test_routing_optimization_endpoint(): void
    {
        $this->mock(\App\Services\GeocodingService::class, function ($mock) {
            $mock->shouldReceive('getCoordinates')->andReturn(['lat' => -23.5, 'lng' => -46.6, 'confidence' => 'high']);
        });

        $payload = [
            'tenant_id_externo' => 'tenant-001',
            'base_operacional_id' => 'base-sp-centro',
            'base_lat' => -23.55,
            'base_lng' => -46.63,
            'data_entrega' => now()->toDateString(),
            'entregas' => [
                ['id' => 101, 'endereco' => 'Rua A, 10', 'cliente' => 'João'],
                ['id' => 102, 'endereco' => 'Av B, 20', 'cliente' => 'Maria'],
            ],
        ];

        $response = $this->postJson('/api/v1/rotas/otimizar', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'otimizada')
            ->assertJsonCount(2, 'paradas');

        $this->assertDatabaseHas('rotas', ['tenant_id_externo' => 'tenant-001']);
        $this->assertDatabaseHas('paradas', ['entrega_id' => 101]);
    }

    public function test_health_endpoint_returns_service_status(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('service', 'ms-005-geocoding');
    }
}
