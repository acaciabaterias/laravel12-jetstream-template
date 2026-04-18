<?php

namespace Tests\Feature;

use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\Filial;
use App\Models\User;
use App\Models\Vale;
use App\Models\ItemVale;
use App\Models\ContaBancaria;
use App\Models\TransacaoFinanceira;
use App\Services\FinanceService;
use App\Services\BankConciliationService;
use App\Services\ProfitabilityAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceModuleTest extends TestCase
{
    use RefreshDatabase;

    private Filial $filial;
    private User $vendedor;
    private ContaBancaria $conta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filial = Filial::create([
            'nome' => 'Filial Financeira',
            'cnpj' => '12.345.678/0001-00',
            'comissao_tipo' => 'percentual',
            'comissao_valor' => 5.00, // 5% de comissão
            'data_fechamento_contabil' => now()->subMonth()->endOfMonth(), // Mês passado fechado
        ]);

        $this->vendedor = User::factory()->create(['filial_id' => $this->filial->id]);
        
        $this->conta = ContaBancaria::create([
            'filial_id' => $this->filial->id,
            'banco' => 'Itaú',
            'agencia' => '0001',
            'conta' => '123456-7',
            'tipo' => 'corrente',
            'status' => 'ativo',
        ]);

        $this->actingAs($this->vendedor);
    }

    public function test_cannot_register_transaction_in_closed_period()
    {
        $service = new FinanceService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Operação bloqueada');

        $service->registrar([
            'conta_id' => $this->conta->id,
            'tipo' => 'receita',
            'categoria' => 'venda',
            'valor' => 100.00,
            'data' => now()->subMonth()->startOfMonth()->format('Y-m-d'), // Data bloqueada
            'filial_id' => $this->filial->id,
        ]);
    }

    public function test_bank_conciliation_matching_logic()
    {
        // 1. Cria transação pendente interna
        $transacao = TransacaoFinanceira::create([
            'conta_id' => $this->conta->id,
            'tipo' => 'receita',
            'categoria' => 'venda',
            'valor' => 500.00,
            'data' => now()->format('Y-m-d'),
            'status' => 'pendente',
        ]);

        $service = new BankConciliationService();
        $results = $service->conciliarConta($this->conta);

        $this->assertEquals(1, $results['conciliados']);
        $this->assertDatabaseHas('transacoes_financeiras', [
            'id' => $transacao->id,
            'status' => 'conciliado',
        ]);
    }

    public function test_profitability_mark_up_calculation()
    {
        // 1. Cria Bateria com custo
        $bateria = Bateria::create([
            'sku' => 'BAT-FIN-001',
            'marca' => 'Heliar',
            'preco_venda' => 500.00,
            'custo_aquisicao' => 300.00, // Custo base
            'filial_id' => $this->filial->id,
        ]);

        // 2. Calcula margem
        $analyzer = new ProfitabilityAnalyzer();
        $result = $analyzer->recolherDadosPeriodo($bateria->id, now()->format('Y-m'));

        // Venda(500) - Custo(300) - Imposto(50) - Comis(25) - Frete(15) = 110.00
        $this->assertEquals(110.00, (float) $result->margem_final);
    }

    public function test_cash_flow_projection_consolidates_correctly()
    {
        // 1. Receita realizada (Conciliada)
        TransacaoFinanceira::create([
            'conta_id' => $this->conta->id,
            'tipo' => 'receita',
            'categoria' => 'venda',
            'valor' => 1000.00,
            'data' => now()->format('Y-m-d'),
            'status' => 'conciliado',
        ]);

        // 2. Receita Pendente (A receber)
        TransacaoFinanceira::create([
            'conta_id' => $this->conta->id,
            'tipo' => 'receita',
            'categoria' => 'venda',
            'valor' => 500.00,
            'data' => now()->addDays(2)->format('Y-m-d'),
            'status' => 'pendente',
        ]);

        // 3. Despesa Pendente (A pagar)
        TransacaoFinanceira::create([
            'conta_id' => $this->conta->id,
            'tipo' => 'despesa',
            'categoria' => 'fornecedor',
            'valor' => 300.00,
            'data' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'pendente',
        ]);

        $service = new FinanceService();
        $projeção = $service->projetarSaldoDiario($this->filial->id, now()->addDays(5));

        // Realizado(1000) + Receber(500) - Pagar(300) = 1200.00
        $this->assertEquals(1200.00, $projeção['saldo_projetado']);
    }
}
