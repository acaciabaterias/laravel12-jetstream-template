<?php

namespace Tests\Feature;

use App\Jobs\SyncBankTransactionsJob;
use App\Livewire\CashFlowPanel;
use App\Livewire\MarginAnalysisGrid;
use App\Models\Bateria;
use App\Models\ContaBancaria;
use App\Models\FechamentoContabil;
use App\Models\OrdemServicoGarantia;
use App\Models\PedidoVenda;
use App\Models\TransacaoFinanceira;
use App\Models\User;
use App\Models\Vale;
use App\Services\ClosingPeriodGuard;
use App\Services\FinanceMatcherProcessor;
use Carbon\CarbonImmutable;
use Livewire\Livewire;
use Tests\TestCase;

class IntelligentFinanceTest extends TestCase
{
    public function test_bank_sync_matches_simple_transactions(): void
    {
        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $cliente = \App\Models\Cliente::factory()->create();
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

        (new SyncBankTransactionsJob($conta->id))->handle(app(\App\Services\BankApiClient::class), app(FinanceMatcherProcessor::class));

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
        $cliente = \App\Models\Cliente::factory()->create();
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

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(ClosingPeriodGuard::class)->ensureOpen($competencia);
    }

    public function test_dashboard_renders_finance_components_for_finance_roles(): void
    {
        $filial = \App\Models\Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'gestor',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeLivewire('finance-dashboard')
            ->assertSeeLivewire('cash-flow-panel')
            ->assertSeeLivewire('margin-analysis-grid');
    }

    public function test_dashboard_hides_finance_components_for_non_finance_roles(): void
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
            ->assertDontSeeLivewire('finance-dashboard')
            ->assertDontSeeLivewire('cash-flow-panel')
            ->assertDontSeeLivewire('margin-analysis-grid');
    }
}
