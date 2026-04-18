<?php

namespace App\Jobs;

use App\Models\Bateria;
use App\Models\OrdemServicoGarantia;
use App\Models\PedidoVenda;
use App\Models\IndiceRetorno;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RecalculateProductReturnIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $bateriaId
    ) {}

    public function handle(): void
    {
        $bateria = Bateria::findOrFail($this->bateriaId);
        $periodo = now()->format('Y-m');

        // Total de vendas (usando PedidoVenda que contém itens de vale ou via itens_pedido se existirem)
        // Simplificação: vamos contar quantos Pedidos de Venda referenciam essa bateria via ItemVale
        $totalVendidas = DB::table('item_vales')
            ->join('vales', 'item_vales.vale_id', '=', 'vales.id')
            ->where('item_vales.bateria_id', $this->bateriaId)
            ->where('vales.status', 'faturado')
            ->sum('item_vales.quantidade');

        // Total de Garantias Procedentes
        $totalGarantias = OrdemServicoGarantia::where('bateria_id', $this->bateriaId)
            ->where('resultado', 'procedente')
            ->count();

        $indice = $totalVendidas > 0 ? ($totalGarantias / $totalVendidas) * 100 : 0;

        // Atualiza Snapshot Historico
        IndiceRetorno::updateOrCreate(
            ['bateria_id' => $this->bateriaId, 'periodo' => $periodo],
            [
                'total_vendidas' => $totalVendidas,
                'total_garantias' => $totalGarantias,
                'indice_calculado' => $indice
            ]
        );

        // Atualiza Cache no Model Bateria
        $bateria->update(['indice_retorno' => $indice]);
    }
}
