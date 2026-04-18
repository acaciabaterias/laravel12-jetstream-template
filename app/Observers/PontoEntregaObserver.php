<?php

namespace App\Observers;

use App\Jobs\ConvertValeToPedidoJob;
use App\Models\PontoEntrega;
use App\Models\Vale;
use Illuminate\Support\Facades\Log;

class PontoEntregaObserver
{
    /**
     * Handle the PontoEntrega "updated" event.
     */
    public function updated(PontoEntrega $pontoEntrega): void
    {
        // Se a parada foi concluída agora
        if ($pontoEntrega->wasChanged('status') && $pontoEntrega->status === 'concluido') {
            $this->processarFechamentoEntrega($pontoEntrega);
        }
    }

    /**
     * Gatilho para faturamento e gestão de sucata física (T015).
     */
    protected function processarFechamentoEntrega(PontoEntrega $pontoEntrega): void
    {
        $vale = $pontoEntrega->vale;

        if (!$vale) {
            return;
        }

        // 1. Dispara o faturamento final (Módulo 005)
        $rota = $pontoEntrega->rotaEntrega;
        
        if ($rota) {
            ConvertValeToPedidoJob::dispatch(
                $vale->id,
                $rota->entregador_id,
                $pontoEntrega->filial_id
            );
        }

        // 2. Ajuste Fino da Sucata (T015)
        // Se o entregador coletou um peso diferente do acordado originalmente, 
        // ou se coletou sucata em vale que não previa, ajustamos o saldo do cliente.
        if ($pontoEntrega->peso_sucata_coletado > 0) {
            $cliente = $pontoEntrega->vale->cliente;
            
            // Se o entregador trouxe peso físico, a gente abate do saldo devedor do cliente
            // ou incrementa crédito se ele trouxe mais do que devia.
            $cliente->decrement('saldo_sucata_kg', $pontoEntrega->peso_sucata_coletado);
            
            Log::info("Sucata coletada na entrega concluída.", [
                'ponto_id' => $pontoEntrega->id,
                'peso' => $pontoEntrega->peso_sucata_coletado,
                'cliente' => $cliente->nome_fantasia
            ]);
        }
    }
}
