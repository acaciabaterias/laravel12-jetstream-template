<?php

namespace App\Observers;

use App\Models\EstoqueMovimentacao;
use App\Models\EstoqueSaldo;
use Exception;
use Illuminate\Support\Facades\DB;

class EstoqueMovimentacaoObserver
{
    /**
     * Handle the EstoqueMovimentacao "creating" event.
     * Prevents the transaction entirely if it would result in negative stock.
     */
    public function creating(EstoqueMovimentacao $movimentacao): void
    {
        // Enforce strict lock-based inventory updates to maintain correct parity
        DB::transaction(function () use ($movimentacao) {
            $saldo = EstoqueSaldo::where('bateria_id', $movimentacao->bateria_id)
                ->where('deposito_id', $movimentacao->deposito_id)
                ->where('filial_id', $movimentacao->filial_id)
                ->lockForUpdate()
                ->first();

            if (! $saldo) {
                if (in_array($movimentacao->tipo, ['saida', 'reserva'])) {
                    throw new Exception("Estoque insuficiente para a operação requisitada. (Id Deposito: {$movimentacao->deposito_id})");
                }

                EstoqueSaldo::create([
                    'bateria_id' => $movimentacao->bateria_id,
                    'deposito_id' => $movimentacao->deposito_id,
                    'filial_id' => $movimentacao->filial_id,
                    'quantidade_atual' => $movimentacao->quantidade,
                    'quantidade_reservada' => 0,
                ]);
            } else {
                $novoSaldo = $saldo->quantidade_atual;
                $novaReserva = $saldo->quantidade_reservada;

                if ($movimentacao->tipo === 'entrada') {
                    $novoSaldo += $movimentacao->quantidade;
                } elseif ($movimentacao->tipo === 'saida') {
                    $novoSaldo -= $movimentacao->quantidade;

                    if ($novoSaldo < $novaReserva) {
                        // Não pode tirar estoque se a quantidade restante for menor do que aquilo que está prometido (reservado) a outros pedidos.
                        // (Nota: se for a conversão de *uma* reserva, o controller deve despachar o estorno_reserva ANTES da saída real)
                        throw new Exception('Estoque insuficiente/comprometido em reservas. (Disponível p/ Venda: '.($saldo->quantidade_atual - $saldo->quantidade_reservada).')');
                    }
                } elseif ($movimentacao->tipo === 'reserva') {
                    $disponível = $saldo->quantidade_atual - $saldo->quantidade_reservada;
                    if ($disponível < $movimentacao->quantidade) {
                        throw new Exception("Estoque físico comprometido com outras reservas. (Disponível p/ Venda Limitado: {$disponível})");
                    }
                    $novaReserva += $movimentacao->quantidade;
                } elseif ($movimentacao->tipo === 'estorno_reserva') {
                    $novaReserva -= $movimentacao->quantidade;
                    // Garante que não zere bugado negativamente
                    if ($novaReserva < 0) {
                        $novaReserva = 0;
                    }
                }

                $saldo->update([
                    'quantidade_atual' => $novoSaldo,
                    'quantidade_reservada' => $novaReserva,
                ]);
            }

        });
    }
}
