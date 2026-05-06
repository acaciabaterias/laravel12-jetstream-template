<?php

namespace Tests\Feature;

use App\Http\Middleware\PrometheusMetrics;
use App\Http\Middleware\TenantConnectionMiddleware;
use App\Jobs\SyncBankTransactionsJob;
use App\Livewire\CashFlowPanel;
use App\Livewire\FinanceDashboard;
use App\Livewire\MarginAnalysisGrid;
use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\ContaBancaria;
use App\Models\FechamentoContabil;
use App\Models\Filial;
use App\Models\OrdemServicoGarantia;
use App\Models\PedidoVenda;
use App\Models\TransacaoFinanceira;
use App\Models\User;
use App\Models\Vale;
use App\Services\BankApiClient;
use App\Services\ClosingPeriodGuard;
use App\Services\FinanceMatcherProcessor;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class IntelligentFinanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PrometheusMetrics::class);

        Route::middleware('web')->get('/finance/tenant-probe', function (Request $request) {
            return response()->json([
                'tenant_host' => config('database.connections.tenant.host'),
                'cliente_id' => optional($request->attributes->get('cliente'))->id,
            ]);
        });
    }

    public function test_bank_sync_matches_simple_transactions(): void
    {
        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $cliente = Cliente::factory()->create();
        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'faturado',
            'data_criacao' => now(),
            'data_faturamento' => now(),
            'created_by' => $user->id,
        ]);
        PedidoVenda::query()->create([
            'vale_id' => $vale->id,
            'cliente_id' => $cliente->id,
            'data_emissao' => now(),
            'valor_total' => 250.00,
            'status' => 'faturado',
        ]);

        $garantia = OrdemServicoGarantia::query()->create([
            'cliente_id' => $cliente->id,
            'bateria_id' => Bateria::create(['sku' => 'FIN-001', 'marca' => 'Moura'])->id,
            'data_abertura' => now(),
            'status' => 'aguardando_pagamento',
            'resultado' => 'improcedente',
            'cobranca_valor' => 180.00,
        ]);

        $conta = ContaBancaria::query()->create([
            'banco' => 'Banco Demo',
            'agencia' => '0001',
            'conta' => '12345-6',
            'tipo' => 'corrente',
            'status' => 'ativa',
        ]);

        (new SyncBankTransactionsJob($conta->id))->handle(app(BankApiClient::class), app(FinanceMatcherProcessor::class));

        $this->assertDatabaseHas('transacoes_financeiras', [
            'origem_tipo' => PedidoVenda::class,
            'origem_id' => PedidoVenda::query()->first()->id,
            'status_conciliado' => true,
        ]);
        $this->assertDatabaseHas('transacoes_financeiras', [
            'origem_tipo' => OrdemServicoGarantia::class,
            'origem_id' => $garantia->id,
            'status_conciliado' => true,
        ]);
    }

    public function test_ambiguous_transactions_become_pending(): void
    {
        $conta = ContaBancaria::query()->create([
            'banco' => 'Banco Demo',
            'agencia' => '0001',
            'conta' => '12345-6',
            'tipo' => 'corrente',
            'status' => 'ativa',
        ]);
        $cliente = Cliente::factory()->create();
        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);

        foreach ([1, 2] as $index) {
            $vale = Vale::query()->create([
                'cliente_id' => $cliente->id,
                'vendedor_id' => $user->id,
                'status' => 'faturado',
                'data_criacao' => now(),
                'data_faturamento' => now(),
                'created_by' => $user->id,
            ]);
            PedidoVenda::query()->create([
                'vale_id' => $vale->id,
                'cliente_id' => $cliente->id,
                'data_emissao' => now(),
                'valor_total' => 500.00,
                'status' => 'faturado',
            ]);
        }

        app(FinanceMatcherProcessor::class)->importAndMatch($conta, [[
            'identificador_externo' => 'bank-ambiguous',
            'tipo' => 'credito',
            'valor' => 500.00,
            'data_transacao' => now()->toDateTimeString(),
            'descricao' => 'Recebimento ambiguo',
        ]]);

        $this->assertDatabaseHas('conciliacoes_pendentes', [
            'motivo' => 'match_ambiguo',
            'status' => 'pendente',
        ]);
    }

    public function test_cash_flow_projection_is_generated(): void
    {
        $gestor = User::factory()->withPersonalTeam()->create(['papel' => 'gestor', 'ativo' => true]);
        $this->actingAs($gestor);

        TransacaoFinanceira::query()->create([
            'conta_bancaria_id' => ContaBancaria::query()->create([
                'banco' => 'Banco Caixa',
                'agencia' => '0002',
                'conta' => '9999-9',
                'tipo' => 'corrente',
                'status' => 'ativa',
            ])->id,
            'tipo' => 'credito',
            'valor' => 100.00,
            'data_transacao' => now(),
            'status_conciliado' => true,
            'descricao' => 'Saldo inicial',
            'identificador_externo' => 'manual-1',
        ]);

        Livewire::test(CashFlowPanel::class)
            ->call('refreshProjection')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('fluxos_caixa_projetado', 1);
    }

    public function test_margin_analysis_calculates_real_margin(): void
    {
        $gestor = User::factory()->withPersonalTeam()->create(['papel' => 'gestor', 'ativo' => true]);
        $this->actingAs($gestor);

        $bateria = Bateria::create([
            'sku' => 'FIN-002',
            'marca' => 'Heliar',
            'preco_venda' => 500,
        ]);

        Livewire::test(MarginAnalysisGrid::class)
            ->set('bateriaId', $bateria->id)
            ->set('custoAquisicao', '300')
            ->set('frete', '20')
            ->set('imposto', '30')
            ->set('comissao', '10')
            ->call('calculate')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('margens_lucro_real', [
            'bateria_id' => $bateria->id,
        ]);
    }

    public function test_closed_period_blocks_financial_changes(): void
    {
        $competencia = CarbonImmutable::parse('2026-04-01');
        FechamentoContabil::query()->create([
            'competencia' => $competencia->format('Y-m'),
            'status' => 'fechado',
            'fechado_em' => now(),
            'fechado_por' => User::factory()->create()->id,
        ]);

        $this->expectException(ValidationException::class);

        app(ClosingPeriodGuard::class)->ensureOpen($competencia);
    }

    public function test_dashboard_renders_finance_components_for_finance_roles(): void
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'gestor',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $this->withoutMiddleware(TenantConnectionMiddleware::class);

        $response = $this->get('/dashboard');

        $response->assertOk()
            ->assertSeeLivewire('finance-dashboard')
            ->assertSeeLivewire('cash-flow-panel')
            ->assertSeeLivewire('margin-analysis-grid');
    }

    public function test_dashboard_hides_finance_components_for_non_finance_roles(): void
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'vendedor',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $this->withoutMiddleware(TenantConnectionMiddleware::class);

        $response = $this->get('/dashboard');

        $response->assertOk()
            ->assertDontSeeLivewire('finance-dashboard')
            ->assertDontSeeLivewire('cash-flow-panel')
            ->assertDontSeeLivewire('margin-analysis-grid');
    }

    public function test_finance_dashboard_renders_cards_chart_and_latest_transactions(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'gestor', 'ativo' => true]);
        $this->actingAs($user);

        $conta = ContaBancaria::query()->create([
            'banco' => 'Banco Fluxo',
            'agencia' => '0101',
            'conta' => '99887-0',
            'tipo' => 'corrente',
            'status' => 'ativa',
        ]);

        TransacaoFinanceira::query()->create([
            'conta_bancaria_id' => $conta->id,
            'tipo' => 'receita',
            'valor' => 1200,
            'data_transacao' => now(),
            'status_conciliado' => true,
            'descricao' => 'Recebimento em balcão',
            'identificador_externo' => 'finance-dashboard-1',
        ]);

        TransacaoFinanceira::query()->create([
            'conta_bancaria_id' => $conta->id,
            'tipo' => 'despesa',
            'valor' => 300,
            'data_transacao' => now()->subDay(),
            'status_conciliado' => false,
            'descricao' => 'Pagamento fornecedor',
            'identificador_externo' => 'finance-dashboard-2',
        ]);

        Livewire::test(FinanceDashboard::class)
            ->assertSee('Receitas registradas no tenant.')
            ->assertSee('Despesas e compromissos lançados.')
            ->assertSee('Fluxo de caixa')
            ->assertSee('Últimas transações')
            ->assertSee('Recebimento em balcão')
            ->assertSee('Pagamento fornecedor');
    }

    public function test_critical_financial_operations_are_audited(): void
    {
        $conta = ContaBancaria::query()->create([
            'banco' => 'Banco Auditoria',
            'agencia' => '0003',
            'conta' => '45678-9',
            'tipo' => 'corrente',
            'status' => 'ativa',
        ]);

        $transacao = TransacaoFinanceira::query()->create([
            'conta_bancaria_id' => $conta->id,
            'tipo' => 'receita',
            'valor' => 500,
            'data_transacao' => now(),
            'status_conciliado' => false,
            'descricao' => 'Auditoria financeira',
            'identificador_externo' => 'audit-finance-1',
        ]);

        $transacao->update([
            'status_conciliado' => true,
        ]);

        FechamentoContabil::query()->create([
            'competencia' => now()->format('Y-m'),
            'status' => 'fechado',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'transacoes_financeiras',
            'action' => 'created',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'transacoes_financeiras',
            'action' => 'updated',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'fechamentos_contabeis',
            'action' => 'created',
        ]);
    }

    public function test_finance_operations_are_isolated_between_tenants_without_cross_access(): void
    {
        $this->withoutMiddleware(PrometheusMetrics::class);

        $tenantA = Cliente::factory()->create([
            'subdominio' => 'fin-a',
            'status' => 'active',
            'supabase_db_host' => 'db-fin-a.supabase.co',
        ]);

        $tenantB = Cliente::factory()->create([
            'subdominio' => 'fin-b',
            'status' => 'active',
            'supabase_db_host' => 'db-fin-b.supabase.co',
        ]);

        $responseA = $this->get('http://fin-a.erp.com/finance/tenant-probe');
        $responseB = $this->get('http://fin-b.erp.com/finance/tenant-probe');

        $responseA->assertOk()->assertJson([
            'tenant_host' => 'db-fin-a.supabase.co',
            'cliente_id' => $tenantA->id,
        ]);

        $responseB->assertOk()->assertJson([
            'tenant_host' => 'db-fin-b.supabase.co',
            'cliente_id' => $tenantB->id,
        ]);
    }
}
