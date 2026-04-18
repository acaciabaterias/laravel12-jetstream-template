<?php

namespace Tests\Feature;

use App\Jobs\ConvertValeToPedidoJob;
use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\Deposito;
use App\Models\EstoqueMovimentacao;
use App\Models\EstoqueSaldo;
use App\Models\Filial;
use App\Models\ItemVale;
use App\Models\User;
use App\Models\Vale;
use App\Services\ReservaEstoqueService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesValesTest extends TestCase
{
    use RefreshDatabase;

    private Filial $filial;

    private User $vendedor;

    private Bateria $bateria;

    private Deposito $deposito;

    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filial = Filial::factory()->create();
        $this->vendedor = User::factory()->create(['filial_id' => $this->filial->id]);
        $this->deposito = Deposito::create(['nome' => 'Loja Principal', 'filial_id' => $this->filial->id]);
        $this->cliente = Cliente::create([
            'cnpj' => '12.345.678/0001-99',
            'razao_social' => 'Cliente Teste Ltda',
            'nome_fantasia' => 'Cliente Teste',
            'email_contato' => 'teste@cliente.com',
            'telefone' => '(11) 99999-9999',
            'subdominio' => 'cliente-teste-vales',
            'supabase_project_ref' => 'proj-test-vales-'.uniqid(),
            'supabase_url' => 'https://test.supabase.co',
            'supabase_db_host' => 'db.test.supabase.co',
            'supabase_db_password' => 'test-password',
            'supabase_anon_key' => 'anon-key',
            'supabase_service_role_key' => 'service-role-key',
        ]);
        $this->bateria = Bateria::create([
            'sku' => 'TEST-001',
            'marca' => 'MarcaTeste',
            'preco_venda' => 250.00,
            'peso_sucata_kg' => 12.0,
            'valor_base_sucata_kg' => 5.00,
            'tem_logistica_reversa' => true,
            'filial_id' => $this->filial->id,
        ]);

        $this->actingAs($this->vendedor);

        // Seed stock: 5 units available
        EstoqueMovimentacao::create([
            'bateria_id' => $this->bateria->id,
            'filial_id' => $this->filial->id,
            'deposito_id' => $this->deposito->id,
            'user_id' => $this->vendedor->id,
            'tipo' => 'entrada',
            'quantidade' => 5,
            'origem' => 'Setup Test',
        ]);
    }

    public function test_reserva_blocks_stock_and_saldo_updates_correctly()
    {
        $service = new ReservaEstoqueService;
        $vale = Vale::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'filial_id' => $this->filial->id,
            'status' => 'aberto',
        ]);

        $service->reservar($this->bateria->id, $this->deposito->id, 3, $this->filial->id, $this->vendedor->id, $vale->id);

        $saldo = EstoqueSaldo::where('bateria_id', $this->bateria->id)->first();
        $this->assertEquals(5, $saldo->quantidade_atual);
        $this->assertEquals(3, $saldo->quantidade_reservada);
    }

    public function test_concurrent_reservation_of_last_unit_is_blocked()
    {
        $service = new ReservaEstoqueService;

        $vale1 = Vale::create(['cliente_id' => $this->cliente->id, 'vendedor_id' => $this->vendedor->id, 'filial_id' => $this->filial->id, 'status' => 'aberto']);
        $vale2 = Vale::create(['cliente_id' => $this->cliente->id, 'vendedor_id' => $this->vendedor->id, 'filial_id' => $this->filial->id, 'status' => 'aberto']);

        // First reservation: 5 units - succeeds
        $service->reservar($this->bateria->id, $this->deposito->id, 5, $this->filial->id, $this->vendedor->id, $vale1->id);

        // Second reservation: any additional unit - must fail
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/comprometido com outras reservas|insuficiente/i');

        $service->reservar($this->bateria->id, $this->deposito->id, 1, $this->filial->id, $this->vendedor->id, $vale2->id);
    }

    public function test_canceling_vale_estorns_all_reservations()
    {
        $service = new ReservaEstoqueService;
        $vale = Vale::create(['cliente_id' => $this->cliente->id, 'vendedor_id' => $this->vendedor->id, 'filial_id' => $this->filial->id, 'status' => 'aberto']);

        $service->reservar($this->bateria->id, $this->deposito->id, 3, $this->filial->id, $this->vendedor->id, $vale->id);

        // Confirm reserva is applied
        $saldo = EstoqueSaldo::where('bateria_id', $this->bateria->id)->first();
        $this->assertEquals(3, $saldo->quantidade_reservada);

        // Estornar
        $service->estornar($this->bateria->id, $this->deposito->id, 3, $this->filial->id, $this->vendedor->id, $vale->id);

        $saldo->refresh();
        $this->assertEquals(0, $saldo->quantidade_reservada);
    }

    public function test_net_price_acrescimo_when_sucata_not_returned()
    {
        // Acréscimo = peso_sucata_kg * valor_base_sucata_kg = 12 * 5 = 60
        $acrescimo = $this->bateria->peso_sucata_kg * $this->bateria->valor_base_sucata_kg;
        $precoFinal = $this->bateria->preco_venda + $acrescimo;

        $this->assertEquals(310.00, $precoFinal);
    }

    public function test_conversion_to_pedido_debits_sucata_on_cliente_when_no_return()
    {
        // Reserve stock first
        $service = new ReservaEstoqueService;
        $vale = Vale::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'filial_id' => $this->filial->id,
            'status' => 'aberto',
        ]);

        $service->reservar($this->bateria->id, $this->deposito->id, 2, $this->filial->id, $this->vendedor->id, $vale->id);

        ItemVale::create([
            'vale_id' => $vale->id,
            'bateria_id' => $this->bateria->id,
            'quantidade' => 2,
            'preco_unitario_original' => 250.00,
            'preco_unitario_final' => 310.00,
            'flag_devolveu_sucata' => false, // did NOT return old battery
        ]);

        // Convert to Pedido - should apply Sucata debit to client
        $job = new ConvertValeToPedidoJob($vale->id, $this->vendedor->id, $this->filial->id);
        $job->handle();

        // 2 batteries * 12kg = 24kg debit on client
        $this->cliente->refresh();
        $this->assertEquals(24.0, $this->cliente->saldo_sucata_kg);

        // Pedido created
        $this->assertDatabaseHas('pedido_vendas', ['vale_id' => $vale->id]);

        // Vale marked as faturado
        $vale->refresh();
        $this->assertEquals('faturado', $vale->status);

        // Estoque actual consumption confirmed (saida applied)
        $saldo = EstoqueSaldo::where('bateria_id', $this->bateria->id)->first();
        $this->assertEquals(3, $saldo->quantidade_atual); // 5 - 2 = 3
        $this->assertEquals(0, $saldo->quantidade_reservada); // reservation resolved
    }

    public function test_conversion_to_pedido_no_sucata_debit_when_returned()
    {
        $service = new ReservaEstoqueService;
        $vale = Vale::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'filial_id' => $this->filial->id,
            'status' => 'aberto',
        ]);

        $service->reservar($this->bateria->id, $this->deposito->id, 1, $this->filial->id, $this->vendedor->id, $vale->id);

        ItemVale::create([
            'vale_id' => $vale->id,
            'bateria_id' => $this->bateria->id,
            'quantidade' => 1,
            'preco_unitario_original' => 250.00,
            'preco_unitario_final' => 250.00,
            'flag_devolveu_sucata' => true, // DID return old battery
        ]);

        $job = new ConvertValeToPedidoJob($vale->id, $this->vendedor->id, $this->filial->id);
        $job->handle();

        // No sucata debit should be applied
        $this->cliente->refresh();
        $this->assertEquals(0.0, (float) $this->cliente->saldo_sucata_kg);
    }
}
