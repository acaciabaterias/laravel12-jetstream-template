<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\Deposito;
use App\Models\EstoqueMovimentacao;
use App\Models\PedidoVenda;
use App\Models\TransacaoFinanceira;
use App\Models\User;
use App\Models\Vale;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationAndDemoSeedersTest extends TestCase
{
    public function test_sales_and_stock_policies_follow_expected_roles(): void
    {
        $gestor = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $vendedor = User::factory()->create(['papel' => 'vendedor', 'ativo' => true]);
        $estoquista = User::factory()->create(['papel' => 'estoquista', 'ativo' => true]);
        $cliente = Cliente::factory()->create();

        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $vendedor->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'created_by' => $gestor->id,
        ]);

        $pedido = PedidoVenda::query()->create([
            'vale_id' => $vale->id,
            'cliente_id' => $cliente->id,
            'data_emissao' => now(),
            'valor_total' => 100,
            'status' => 'pendente',
        ]);

        $bateria = Bateria::query()->create([
            'sku' => 'BAT-POLICY-001',
            'marca' => 'Moura',
            'preco_venda' => 100,
            'peso_sucata_kg' => 5,
            'valor_base_sucata_kg' => 2,
        ]);

        $deposito = Deposito::query()->create([
            'nome' => 'Policy Demo',
            'tipo' => 'principal',
            'status' => 'ativo',
        ]);

        $movimentacao = EstoqueMovimentacao::query()->create([
            'bateria_id' => $bateria->id,
            'deposito_id' => $deposito->id,
            'user_id' => $estoquista->id,
            'tipo_operacao' => 'entrada',
            'quantidade' => 1,
            'data_movimentacao' => now(),
        ]);

        $this->assertTrue(Gate::forUser($vendedor)->allows('create', Vale::class));
        $this->assertTrue(Gate::forUser($gestor)->allows('faturar', $pedido));
        $this->assertFalse(Gate::forUser($estoquista)->allows('create', Vale::class));
        $this->assertTrue(Gate::forUser($estoquista)->allows('view', $movimentacao));
        $this->assertFalse(Gate::forUser($vendedor)->allows('view', $movimentacao));
    }

    public function test_financial_policy_and_additional_gates_are_registered(): void
    {
        $dono = User::factory()->create(['papel' => 'dono', 'ativo' => true]);
        $vendedor = User::factory()->create(['papel' => 'vendedor', 'ativo' => true]);

        $transacao = TransacaoFinanceira::factory()->create([
            'status_conciliado' => false,
        ]);

        $this->assertTrue(Gate::forUser($dono)->allows('conciliate', $transacao));
        $this->assertFalse(Gate::forUser($vendedor)->allows('conciliate', $transacao));
        $this->assertTrue(Gate::forUser($dono)->allows('emitir-documentos-fiscais'));
        $this->assertFalse(Gate::forUser($vendedor)->allows('processar-cnab'));
    }

    public function test_demo_seeders_populate_central_operational_and_finance_data(): void
    {
        $this->seed(\Database\Seeders\DemoCentralSeeder::class);
        $this->seed(\Database\Seeders\DemoOperationalSeeder::class);
        $this->seed(\Database\Seeders\DemoFinanceSeeder::class);

        $this->assertDatabaseHas('usuarios_plataforma', [
            'email' => 'demo.superadmin@bateriaexpert.test',
        ]);

        $this->assertDatabaseHas('clientes', [
            'subdominio' => 'demo-central',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'dono.demo@bateriaexpert.test',
        ]);

        $this->assertDatabaseHas('baterias', [
            'sku' => 'BAT-DEMO-001',
        ]);

        $this->assertDatabaseHas('contas_bancarias', [
            'conta' => '12345-6',
        ]);
    }
}
