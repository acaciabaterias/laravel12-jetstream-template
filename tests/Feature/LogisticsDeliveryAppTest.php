<?php

namespace Tests\Feature;

use App\Livewire\DeliveryRouteScreen;
use App\Livewire\RoutePlanner;
use App\Models\Cliente;
use App\Models\PontoEntrega;
use App\Models\RecebimentoMovel;
use App\Models\RotaEntrega;
use App\Models\SyncEvento;
use App\Models\User;
use App\Models\Vale;
use App\Services\DeliverySyncService;
use App\Services\RouteCloseValidator;
use Livewire\Livewire;
use Tests\TestCase;

class LogisticsDeliveryAppTest extends TestCase
{
    public function test_route_planner_creates_route_and_stop(): void
    {
        $gestor = User::factory()->withPersonalTeam()->create(['papel' => 'gestor', 'ativo' => true]);
        $entregador = User::factory()->create(['papel' => 'entregador', 'ativo' => true]);
        $cliente = Cliente::factory()->create();
        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $gestor->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'observacoes' => 'Entrega de teste',
            'created_by' => $gestor->id,
        ]);

        $this->actingAs($gestor);

        Livewire::test(RoutePlanner::class)
            ->set('entregadorId', $entregador->id)
            ->call('createRoute')
            ->set('clienteId', $cliente->id)
            ->set('valeId', $vale->id)
            ->set('enderecoEntrega', 'Rua das Flores, 100')
            ->call('addStop')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('rotas_entrega', [
            'entregador_id' => $entregador->id,
        ]);
        $this->assertDatabaseHas('pontos_entrega', [
            'cliente_id' => $cliente->id,
            'vale_id' => $vale->id,
        ]);
    }

    public function test_delivery_sync_service_is_idempotent_for_duplicate_receipts(): void
    {
        $entregador = User::factory()->create(['papel' => 'entregador', 'ativo' => true]);
        $cliente = Cliente::factory()->create();
        $rota = RotaEntrega::query()->create([
            'entregador_id' => $entregador->id,
            'data_rota' => now()->toDateString(),
            'status' => 'em_rota',
        ]);
        $ponto = PontoEntrega::query()->create([
            'rota_entrega_id' => $rota->id,
            'cliente_id' => $cliente->id,
            'endereco_entrega' => 'Rua Sync, 55',
            'ordem_parada' => 1,
            'status' => 'planejado',
        ]);

        $payload = [
            'dispositivo_uuid' => 'device-001',
            'entidade_tipo' => RecebimentoMovel::class,
            'ponto_entrega_id' => $ponto->id,
            'valor' => 150.50,
            'metodo_pagamento' => 'pix',
        ];

        $service = app(DeliverySyncService::class);
        $service->sync($payload);
        $service->sync($payload);

        $this->assertSame(1, SyncEvento::query()->count());
        $this->assertSame(1, RecebimentoMovel::query()->count());
    }

    public function test_delivery_screen_registers_payment_and_updates_stop(): void
    {
        $entregador = User::factory()->withPersonalTeam()->create(['papel' => 'entregador', 'ativo' => true]);
        $cliente = Cliente::factory()->create();
        $rota = RotaEntrega::query()->create([
            'entregador_id' => $entregador->id,
            'data_rota' => now()->toDateString(),
            'status' => 'em_rota',
        ]);
        $ponto = PontoEntrega::query()->create([
            'rota_entrega_id' => $rota->id,
            'cliente_id' => $cliente->id,
            'endereco_entrega' => 'Rua A, 10',
            'ordem_parada' => 1,
            'status' => 'planejado',
        ]);

        $this->actingAs($entregador);

        Livewire::test(DeliveryRouteScreen::class)
            ->set('rotaEntregaId', $rota->id)
            ->set('pontoEntregaId', $ponto->id)
            ->set('valorRecebido', '99.90')
            ->set('metodoPagamento', 'pix')
            ->call('registerPayment')
            ->set('pesoSucataColetado', '12.30')
            ->set('observacao', 'Entrega concluida')
            ->call('updateStop')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recebimentos_moveis', [
            'ponto_entrega_id' => $ponto->id,
            'status_sincronizado' => true,
        ]);
        $this->assertDatabaseHas('pontos_entrega', [
            'id' => $ponto->id,
            'status' => 'concluido',
        ]);
    }

    public function test_route_close_validator_blocks_pending_route(): void
    {
        $entregador = User::factory()->create(['papel' => 'entregador', 'ativo' => true]);
        $cliente = Cliente::factory()->create();
        $rota = RotaEntrega::query()->create([
            'entregador_id' => $entregador->id,
            'data_rota' => now()->toDateString(),
            'status' => 'em_rota',
        ]);
        PontoEntrega::query()->create([
            'rota_entrega_id' => $rota->id,
            'cliente_id' => $cliente->id,
            'endereco_entrega' => 'Rua B, 20',
            'ordem_parada' => 1,
            'status' => 'planejado',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(RouteCloseValidator::class)->assertCanClose($rota->fresh('pontos.recebimentos'));
    }

    public function test_dashboard_renders_logistics_components_for_delivery_roles(): void
    {
        $filial = \App\Models\Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'entregador',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeLivewire('route-planner')
            ->assertSeeLivewire('logistics-dashboard')
            ->assertSeeLivewire('delivery-route-screen');
    }

    public function test_dashboard_hides_logistics_components_for_non_delivery_roles(): void
    {
        $filial = \App\Models\Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'vendedor',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertDontSeeLivewire('route-planner')
            ->assertDontSeeLivewire('logistics-dashboard')
            ->assertDontSeeLivewire('delivery-route-screen');
    }
}
