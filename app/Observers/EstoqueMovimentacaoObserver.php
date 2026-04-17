<?php

namespace App\Observers;

use App\Models\EstoqueMovimentacao;
use App\Models\EstoqueSaldo;
use Illuminate\Support\Facades\DB;
use Exception;

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

            if (!$saldo) {
                if ($movimentacao->tipo === 'saida') {
                    throw new Exception("Estoque insuficiente para realizar saída.");
                }

                EstoqueSaldo::create([
                    'bateria_id' => $movimentacao->bateria_id,
                    'deposito_id' => $movimentacao->deposito_id,
                    'filial_id' => $movimentacao->filial_id,
                    'quantidade_atual' => $movimentacao->quantidade
                ]);
            } else {
                $novoSaldo = $saldo->quantidade_atual;

                if ($movimentacao->tipo === 'entrada') {
                    $novoSaldo += $movimentacao->quantidade;
                } elseif ($movimentacao->tipo === 'saida') {
                    $novoSaldo -= $movimentacao->quantidade;
                    
                    if ($novoSaldo < 0) {
                        throw new Exception("Estoque insuficiente. Saldo disponível: {$saldo->quantidade_atual}");
                    }
                }

                $saldo->update(['quantidade_atual' => $novoSaldo]);
            }
        });
    }
}
