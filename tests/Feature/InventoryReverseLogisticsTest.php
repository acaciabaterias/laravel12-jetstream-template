<?php

namespace Tests\Feature;

use App\Livewire\ContaSucataDashboard;
use App\Livewire\EstoqueDashboard;
use App\Livewire\XmlImportForm;
use App\Models\Bateria;
use App\Models\ContaSucataMovimentacao;
use App\Models\Deposito;
use App\Models\Filial;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Services\EstoqueSaldoService;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryReverseLogisticsTest extends TestCase
{
    public function test_stock_movements_update_consolidated_balance(): void
    {
        $user = User::factory()->create(['papel' => 'estoquista', 'ativo' => true]);
        $this->actingAs($user);

        $bateria = Bateria::create([
            'sku' => 'INV-001',
            'marca' => 'Moura',
        ]);

        $deposito = Deposito::create([
            'nome' => 'Principal',
            'tipo' => 'principal',
            'status' => 'ativo',
        ]);

        $service = app(EstoqueSaldoService::class);
        $service->registrarMovimentacao($bateria, $deposito, 10, 'entrada', $user, 'compra_xml');
        $service->registrarMovimentacao($bateria, $deposito, 3, 'saida', $user, 'os');

        $this->assertDatabaseHas('estoque_saldos', [
            'bateria_id' => $bateria->id,
            'deposito_id' => $deposito->id,
            'quantidade_atual' => 7,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'estoque_movimentacoes',
            'action' => 'created',
        ]);
    }

    public function test_negative_stock_is_blocked(): void
    {
        $user = User::factory()->create(['papel' => 'estoquista', 'ativo' => true]);
        $this->actingAs($user);

        $bateria = Bateria::create([
            'sku' => 'INV-002',
            'marca' => 'Heliar',
        ]);

        $deposito = Deposito::create([
            'nome' => 'Tecnico',
            'tipo' => 'tecnico',
            'status' => 'ativo',
        ]);

        $this->expectException(ValidationException::class);

        app(EstoqueSaldoService::class)
            ->registrarMovimentacao($bateria, $deposito, 1, 'saida', $user, 'ajuste_manual');
    }

    public function test_xml_import_form_blocks_duplicate_invoice_key(): void
    {
        $user = User::factory()->create(['papel' => 'estoquista', 'ativo' => true]);
        $this->actingAs($user);

        XmlImportacao::create([
            'chave_nfe' => str_repeat('1', 44),
            'status' => 'processado',
            'payload_xml' => ['raw' => '<nfe />'],
        ]);

        Livewire::test(XmlImportForm::class)
            ->set('chaveNfe', str_repeat('1', 44))
            ->set('payloadXml', '<nfe>duplicada</nfe>')
            ->call('importar')
            ->assertHasErrors(['chaveNfe' => 'unique']);
    }

    public function test_scrap_account_keeps_running_balance(): void
    {
        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $this->actingAs($user);

        $bateria = Bateria::create([
            'sku' => 'INV-003',
            'marca' => 'Bosch',
        ]);

        Livewire::test(ContaSucataDashboard::class)
            ->set('bateriaId', $bateria->id)
            ->set('tipoMovimento', 'credito')
            ->set('quantidadeKg', 2.5)
            ->set('valorUnitario', 4.20)
            ->set('origem', 'retorno_cliente')
            ->call('registrarMovimento');

        $movimentacao = ContaSucataMovimentacao::query()->latest('id')->first();

        $this->assertNotNull($movimentacao);
        $this->assertSame('10.50', $movimentacao->saldo_resultante);
    }

    public function test_inventory_dashboard_route_renders_components_for_stock_roles(): void
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'estoquista',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeLivewire('estoque-dashboard')
            ->assertSeeLivewire('estoque-adjustment-form')
            ->assertSeeLivewire('xml-import-form')
            ->assertSeeLivewire('conta-sucata-dashboard');
    }

    public function test_estoque_dashboard_renders_cards_chart_and_top_sellers(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'estoquista', 'ativo' => true]);
        $this->actingAs($user);

        $bateria = Bateria::query()->create([
            'sku' => 'INV-004',
            'marca' => 'Moura',
            'preco_venda' => 450,
        ]);

        $deposito = Deposito::query()->create([
            'nome' => 'Loja 01',
            'tipo' => 'principal',
            'status' => 'ativo',
        ]);

        app(EstoqueSaldoService::class)->registrarMovimentacao($bateria, $deposito, 12, 'entrada', $user, 'compra_xml');
        app(EstoqueSaldoService::class)->registrarMovimentacao($bateria, $deposito, 3, 'saida', $user, 'pedido_venda');

        $cliente = \App\Models\Cliente::factory()->create();
        $vale = \App\Models\Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'faturado',
            'data_criacao' => now(),
            'created_by' => $user->id,
        ]);

        $vale->itens()->create([
            'bateria_id' => $bateria->id,
            'quantidade' => 3,
            'preco_unitario_original' => 450,
            'preco_unitario_final' => 450,
            'flag_devolveu_sucata' => true,
        ]);

        Livewire::test(EstoqueDashboard::class)
            ->assertSee('Itens com saldo igual ou abaixo de 5.')
            ->assertSee('Soma dos saldos disponíveis.')
            ->assertSee('Produtos mais vendidos')
            ->assertSee('Saídas por período')
            ->assertSee('INV-004')
            ->assertSee('Loja 01');
    }
}
