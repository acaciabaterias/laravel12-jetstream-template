<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\Deposito;
use App\Models\EstoqueSaldo;
use App\Models\ItemVale;
use App\Models\OrdemServico;
use App\Models\OrdemServicoGarantia;
use App\Models\PedidoVenda;
use App\Models\User;
use App\Models\Vale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoOperationalSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('clientes') || ! Schema::hasTable('baterias')) {
            return;
        }

        $dono = User::query()->firstOrCreate(
            ['email' => 'dono.demo@bateriaexpert.test'],
            ['name' => 'Dono Demo', 'password' => 'password', 'papel' => 'dono', 'ativo' => true],
        );

        $vendedor = User::query()->firstOrCreate(
            ['email' => 'vendedor.demo@bateriaexpert.test'],
            ['name' => 'Vendedor Demo', 'password' => 'password', 'papel' => 'vendedor', 'ativo' => true],
        );

        $tecnico = User::query()->firstOrCreate(
            ['email' => 'tecnico.demo@bateriaexpert.test'],
            ['name' => 'Técnico Demo', 'password' => 'password', 'papel' => 'tecnico', 'ativo' => true],
        );

        $cliente = Cliente::query()->firstOrCreate(
            ['subdominio' => 'demo-operacao'],
            [
                'cnpj' => '98765432000100',
                'razao_social' => 'BateriaExpert Demo Operação',
                'nome_fantasia' => 'Demo Operação',
                'email_contato' => 'operacao@bateriaexpert.test',
                'telefone' => '11999997777',
                'endereco' => 'Rua Operacional, 200',
                'saldo_sucata_kg' => 180,
                'plano' => 'essential',
                'status' => 'active',
                'subscription_ends_at' => now()->addMonth(),
                'supabase_project_ref' => 'demo-operacao-ref',
                'supabase_url' => 'https://demo-operacao.supabase.co',
                'supabase_db_host' => 'db.demo-operacao.supabase.co',
                'supabase_db_password' => 'demo-password',
                'supabase_anon_key' => 'demo-anon-key',
                'supabase_service_role_key' => 'demo-service-role-key',
            ],
        );

        $bateria = Bateria::query()->firstOrCreate(
            ['sku' => 'BAT-DEMO-001'],
            [
                'marca' => 'Moura',
                'tecnologia' => 'AGM',
                'amperagem' => 60,
                'polo' => 'D',
                'preco_venda' => 550,
                'peso_sucata_kg' => 12.5,
                'valor_base_sucata_kg' => 4.8,
                'tem_logistica_reversa' => true,
            ],
        );

        $deposito = null;

        if (Schema::hasTable('depositos')) {
            $deposito = Deposito::query()->firstOrCreate(
                ['nome' => 'Depósito Principal Demo'],
                ['tipo' => 'principal', 'status' => 'ativo'],
            );
        }

        if ($deposito instanceof Deposito && Schema::hasTable('estoque_saldos')) {
            EstoqueSaldo::query()->updateOrCreate(
                ['bateria_id' => $bateria->id, 'deposito_id' => $deposito->id],
                ['quantidade_atual' => 40],
            );
        }

        if (Schema::hasTable('vales')) {
            $vale = Vale::query()->firstOrCreate(
                ['observacoes' => 'Vale demo operacional'],
                [
                    'cliente_id' => $cliente->id,
                    'vendedor_id' => $vendedor->id,
                    'status' => 'aberto',
                    'data_criacao' => now(),
                    'created_by' => $dono->id,
                ],
            );

            if (Schema::hasTable('itens_vale')) {
                ItemVale::query()->updateOrCreate(
                    ['vale_id' => $vale->id, 'bateria_id' => $bateria->id],
                    [
                        'quantidade' => 2,
                        'preco_unitario_original' => 550,
                        'preco_unitario_final' => 550,
                        'flag_devolveu_sucata' => true,
                    ],
                );
            }

            if (Schema::hasTable('pedidos_venda')) {
                PedidoVenda::query()->firstOrCreate(
                    ['vale_id' => $vale->id],
                    [
                        'cliente_id' => $cliente->id,
                        'data_emissao' => now(),
                        'valor_total' => 1100,
                        'status' => 'faturado',
                    ],
                );
            }

            if (Schema::hasTable('ordens_servico')) {
                OrdemServico::query()->firstOrCreate(
                    ['vale_id' => $vale->id],
                    [
                        'cliente_id' => $cliente->id,
                        'tecnico_responsavel_id' => $tecnico->id,
                        'data_abertura' => now(),
                        'status' => 'aberta',
                        'laudo' => 'Diagnóstico inicial da bateria.',
                    ],
                );
            }

            if (Schema::hasTable('ordens_servico_garantia')) {
                OrdemServicoGarantia::query()->firstOrCreate(
                    ['vale_original_id' => $vale->id],
                    [
                        'cliente_id' => $cliente->id,
                        'bateria_id' => $bateria->id,
                        'data_abertura' => now(),
                        'status' => 'em_analise',
                        'laudo' => 'Garantia em análise.',
                        'resultado' => 'procedente',
                    ],
                );
            }
        }
    }
}
