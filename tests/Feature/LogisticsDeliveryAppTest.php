<?php

namespace Tests\Feature;

use App\Http\Middleware\PrometheusMetrics;
use App\Jobs\SyncDeliveryEventsJob;
use App\Livewire\DeliveryRouteScreen;
use App\Livewire\RoutePlanner;
use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\Filial;
use App\Models\GeolocalizacaoEvento;
use App\Models\PontoEntrega;
use App\Models\RecebimentoMovel;
use App\Models\RotaEntrega;
use App\Models\SyncEvento;
use App\Models\User;
use App\Models\Vale;
use App\Services\DeliverySyncService;
use App\Services\RouteCloseValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class LogisticsDeliveryAppTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/logistics/tenant-probe', function (Request $request) {
            return response()->json([
                'tenant_host' => config('database.connections.tenant.host'),
                'cliente_id' => optional($request->attributes->get('cliente'))->id,
            ]);
        });
    }

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

        $this->expectException(ValidationException::class);

        app(RouteCloseValidator::class)->assertCanClose($rota->fresh('pontos.recebimentos'));
    }

    public function test_dashboard_renders_logistics_components_for_delivery_roles(): void
    {
        $filial = Filial::factory()->create();
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
        $filial = Filial::factory()->create();
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

    public function test_offline_shift_events_are_synchronized_in_order_after_reconnect(): void
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
            'endereco_entrega' => 'Rua Offline, 99',
            'ordem_parada' => 1,
            'status' => 'planejado',
        ]);

        $eventosOffline = [
            [
                'dispositivo_uuid' => 'device-turno-01',
                'entidade_tipo' => GeolocalizacaoEvento::class,
                'rota_entrega_id' => $rota->id,
                'ponto_entrega_id' => $ponto->id,
                'latitude' => -23.55052,
                'longitude' => -46.63331,
                'tipo_evento' => 'checkin',
                'recorded_at' => now()->subMinutes(30)->toDateTimeString(),
            ],
            [
                'dispositivo_uuid' => 'device-turno-01',
                'entidade_tipo' => RecebimentoMovel::class,
                'ponto_entrega_id' => $ponto->id,
                'valor' => 75.00,
                'metodo_pagamento' => 'pix',
            ],
            [
                'dispositivo_uuid' => 'device-turno-01',
                'entidade_tipo' => PontoEntrega::class,
                'entidade_id' => $ponto->id,
                'peso_sucata_coletado' => 9.50,
                'status' => 'concluido',
                'observacao' => 'Turno sincronizado',
            ],
        ];

        foreach ($eventosOffline as $payload) {
            (new SyncDeliveryEventsJob($payload))->handle(app(DeliverySyncService::class));
        }

        $this->assertSame(3, SyncEvento::query()->count());
        $this->assertDatabaseHas('sync_eventos', ['status' => 'processado']);
        $this->assertDatabaseHas('pontos_entrega', [
            'id' => $ponto->id,
            'status' => 'concluido',
        ]);
    }

    public function test_delivery_screen_supports_split_mobile_payments(): void
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
            'endereco_entrega' => 'Rua Pagamento, 10',
            'ordem_parada' => 1,
            'status' => 'planejado',
        ]);

        $this->actingAs($entregador);

        Livewire::test(DeliveryRouteScreen::class)
            ->set('rotaEntregaId', $rota->id)
            ->set('pontoEntregaId', $ponto->id)
            ->set('valorRecebido', '60.00')
            ->set('metodoPagamento', 'pix')
            ->call('registerPayment')
            ->set('valorRecebido', '40.00')
            ->set('metodoPagamento', 'cartao')
            ->call('registerPayment')
            ->assertHasNoErrors();

        $this->assertSame(
            2,
            RecebimentoMovel::query()->where('ponto_entrega_id', $ponto->id)->count()
        );
        $this->assertSame(
            '100.00',
            number_format((float) RecebimentoMovel::query()->where('ponto_entrega_id', $ponto->id)->sum('valor'), 2, '.', '')
        );
    }

    public function test_logistics_operations_are_isolated_between_tenants_without_cross_access(): void
    {
        $this->withoutMiddleware(PrometheusMetrics::class);

        $tenantA = Cliente::factory()->create([
            'subdominio' => 'log-a',
            'status' => 'active',
            'supabase_db_host' => 'db-log-a.supabase.co',
        ]);
        $tenantB = Cliente::factory()->create([
            'subdominio' => 'log-b',
            'status' => 'active',
            'supabase_db_host' => 'db-log-b.supabase.co',
        ]);

        $responseA = $this->get('http://log-a.erp.com/logistics/tenant-probe');
        $responseB = $this->get('http://log-b.erp.com/logistics/tenant-probe');

        $responseA->assertOk()->assertJson([
            'tenant_host' => 'db-log-a.supabase.co',
            'cliente_id' => $tenantA->id,
        ]);
        $responseB->assertOk()->assertJson([
            'tenant_host' => 'db-log-b.supabase.co',
            'cliente_id' => $tenantB->id,
        ]);
    }

    public function test_field_scrap_adjustment_recalculates_financial_balance(): void
    {
        $entregador = User::factory()->create(['papel' => 'entregador', 'ativo' => true]);
        $cliente = Cliente::factory()->create();
        $rota = RotaEntrega::query()->create([
            'entregador_id' => $entregador->id,
            'data_rota' => now()->toDateString(),
            'status' => 'em_rota',
        ]);
        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $entregador->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'created_by' => $entregador->id,
        ]);
        $bateria = Bateria::create([
            'sku' => 'BAT-LOG-001',
            'marca' => 'Moura',
            'preco_venda' => 100,
            'peso_sucata_kg' => 10,
            'valor_base_sucata_kg' => 4.5,
        ]);
        $vale->itens()->create([
            'bateria_id' => $bateria->id,
            'quantidade' => 2,
            'preco_unitario_original' => 100,
            'preco_unitario_final' => 100,
            'flag_devolveu_sucata' => true,
        ]);
        $ponto = PontoEntrega::query()->create([
            'rota_entrega_id' => $rota->id,
            'vale_id' => $vale->id,
            'cliente_id' => $cliente->id,
            'endereco_entrega' => 'Rua Sucata, 1',
            'ordem_parada' => 1,
            'status' => 'planejado',
        ]);

        app(DeliverySyncService::class)->sync([
            'dispositivo_uuid' => 'device-sucata-01',
            'entidade_tipo' => PontoEntrega::class,
            'entidade_id' => $ponto->id,
            'peso_sucata_coletado' => 8.0,
            'status' => 'concluido',
            'observacao' => 'Ajuste coletado',
        ]);

        $this->assertDatabaseHas('conta_sucata_movimentacoes', [
            'entidade_tipo' => PontoEntrega::class,
            'entidade_id' => $ponto->id,
            'origem' => 'logistica_sucata',
            'quantidade_kg' => '8.00',
            'valor_unitario' => '4.50',
            'saldo_resultante' => '36.00',
        ]);
    }

    public function test_tracking_persists_only_relevant_operational_events(): void
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
            'endereco_entrega' => 'Rua Eventos, 15',
            'ordem_parada' => 1,
            'status' => 'planejado',
        ]);

        app(DeliverySyncService::class)->sync([
            'dispositivo_uuid' => 'device-geo-01',
            'entidade_tipo' => GeolocalizacaoEvento::class,
            'rota_entrega_id' => $rota->id,
            'ponto_entrega_id' => $ponto->id,
            'latitude' => -23.55052,
            'longitude' => -46.63331,
            'tipo_evento' => 'checkin',
            'recorded_at' => now()->toDateTimeString(),
        ]);

        app(DeliverySyncService::class)->sync([
            'dispositivo_uuid' => 'device-geo-02',
            'entidade_tipo' => GeolocalizacaoEvento::class,
            'rota_entrega_id' => $rota->id,
            'ponto_entrega_id' => $ponto->id,
            'latitude' => -23.55050,
            'longitude' => -46.63330,
            'tipo_evento' => 'heartbeat_debug',
            'recorded_at' => now()->addSecond()->toDateTimeString(),
        ]);

        $this->assertSame(1, GeolocalizacaoEvento::query()->count());
        $this->assertDatabaseMissing('geolocalizacao_eventos', [
            'tipo_evento' => 'heartbeat_debug',
        ]);
        $this->assertSame(2, SyncEvento::query()->count());
    }

    public function test_logistics_layout_registers_pwa_assets_and_indexeddb_storage(): void
    {
        $this->assertFileExists(public_path('manifest.webmanifest'));
        $this->assertFileExists(public_path('sw.js'));

        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertNotFalse($layout);
        $this->assertStringContainsString('manifest.webmanifest', (string) $layout);
        $this->assertStringContainsString("navigator.serviceWorker.register('/sw.js')", (string) $layout);
        $this->assertStringContainsString("indexedDB.open('bx-logistics-offline', 1)", (string) $layout);
    }
}
