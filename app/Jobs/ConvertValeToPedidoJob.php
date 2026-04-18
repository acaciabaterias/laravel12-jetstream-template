<?php

namespace App\Jobs;

use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\EstoqueMovimentacao;
use App\Models\PedidoVenda;
use App\Models\Vale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ConvertValeToPedidoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $valeId;

    public $userId;

    public $filialId;

    public function __construct(int $valeId, int $userId, int $filialId)
    {
        $this->valeId = $valeId;
        $this->userId = $userId;
        $this->filialId = $filialId;
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $vale = Vale::with('itens.bateria')->findOrFail($this->valeId);

            if ($vale->status !== 'aberto') {
                throw new \Exception('Apenas vales abertos podem ser faturados.');
            }

            $totalLiquido = 0;
            $cliente = Cliente::find($vale->cliente_id);

            // Fetch generic depot (in real logic it is selected on UI, we fallback for now)
            $depositoId = \App\Models\Deposito::where('filial_id', $this->filialId)->first()->id;

            foreach ($vale->itens as $item) {
                $totalLiquido += ($item->quantidade * $item->preco_unitario_final);

                // 1. Estorna a Reserva que bloqueava os produtos no carrinho
                EstoqueMovimentacao::create([
                    'bateria_id' => $item->bateria_id,
                    'filial_id' => $this->filialId,
                    'deposito_id' => $depositoId,
                    'user_id' => $this->userId,
                    'tipo' => 'estorno_reserva',
                    'quantidade' => $item->quantidade,
                    'origem' => 'Faturamento',
                    'referencia' => 'Efetivação de Vale: '.$vale->id,
                ]);

                // 2. Registra Saída imediata Definitiva
                EstoqueMovimentacao::create([
                    'bateria_id' => $item->bateria_id,
                    'filial_id' => $this->filialId,
                    'deposito_id' => $depositoId,
                    'user_id' => $this->userId,
                    'tipo' => 'saida',
                    'quantidade' => $item->quantidade,
                    'origem' => 'Venda',
                    'referencia' => 'Pedido Fechado sobre Vale: '.$vale->id,
                ]);

                // 3. Verifica Débito Sucata
                if (! $item->flag_devolveu_sucata && $item->bateria->peso_sucata_kg) {
                    // O cliente levou a bateria e NÃO deixou o casco de troca. Incrementa débito no cliente.
                    $cliente->saldo_sucata_kg += ($item->bateria->peso_sucata_kg * $item->quantidade);

                    if ($item->bateria->valor_base_sucata_kg) {
                        $cliente->saldo_sucata_financeiro += (
                            $item->bateria->peso_sucata_kg * $item->bateria->valor_base_sucata_kg * $item->quantidade
                        );
                    }
                }
            }

            $cliente->save();

            // 4. Cria o Pedido Referencial
            PedidoVenda::create([
                'vale_id' => $vale->id,
                'cliente_id' => $vale->cliente_id,
                'filial_id' => $this->filialId,
                'valor_total' => $totalLiquido,
                'status' => 'pago', // Assumindo fluxo simplificado de Caixa Rápido POS
            ]);

            $vale->status = 'faturado';
            $vale->data_faturamento = now();
            $vale->save();

            // 5. Integração Módulo 008 - Financeiro Inteligente
            $conta = \App\Models\ContaBancaria::where('filial_id', $this->filialId)->first();
            if ($conta) {
                (new \App\Services\FinanceService())->registrar([
                    'conta_id' => $conta->id,
                    'tipo' => 'receita',
                    'categoria' => 'venda',
                    'valor' => $totalLiquido,
                    'data' => now()->format('Y-m-d'),
                    'status' => 'pendente', // Aguardando conciliação bancária
                    'vale_id' => $vale->id,
                    'origem' => "Faturamento de Vale #{$vale->id}",
                ]);
            }

            // 6. Disparar recálculo de rentabilidade (Módulo 008)
            $analyzer = new \App\Services\ProfitabilityAnalyzer();
            foreach ($vale->itens as $item) {
                $analyzer->recolherDadosPeriodo($item->bateria_id, now()->format('Y-m'));
            }
        });
    }
}
