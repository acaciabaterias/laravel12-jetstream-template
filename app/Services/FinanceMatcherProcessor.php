<?php

namespace App\Services;

use App\Models\ConciliacaoPendente;
use App\Models\ContaBancaria;
use App\Models\OrdemServicoGarantia;
use App\Models\PedidoVenda;
use App\Models\TransacaoFinanceira;

class FinanceMatcherProcessor
{
    public function importAndMatch(ContaBancaria $contaBancaria, array $transactions): void
    {
        foreach ($transactions as $transaction) {
            $transacao = TransacaoFinanceira::query()->firstOrCreate(
                [
                    'identificador_externo' => $transaction['identificador_externo'],
                ],
                [
                    'conta_bancaria_id' => $contaBancaria->id,
                    'tipo' => $transaction['tipo'],
                    'valor' => $transaction['valor'],
                    'data_transacao' => $transaction['data_transacao'],
                    'status_conciliado' => false,
                    'descricao' => $transaction['descricao'],
                ],
            );

            $pedidoMatches = PedidoVenda::query()
                ->where('status', 'faturado')
                ->where('valor_total', $transacao->valor)
                ->get();

            $garantiaMatches = OrdemServicoGarantia::query()
                ->where('resultado', 'improcedente')
                ->where('cobranca_valor', $transacao->valor)
                ->get();

            $totalMatches = $pedidoMatches->count() + $garantiaMatches->count();

            if ($totalMatches === 1) {
                $match = $pedidoMatches->first() ?? $garantiaMatches->first();

                $transacao->update([
                    'status_conciliado' => true,
                    'origem_tipo' => $match::class,
                    'origem_id' => $match->id,
                ]);

                continue;
            }

            ConciliacaoPendente::query()->updateOrCreate(
                [
                    'transacao_financeira_id' => $transacao->id,
                ],
                [
                    'motivo' => $totalMatches === 0 ? 'sem_match' : 'match_ambiguo',
                    'payload_bancario' => $transaction,
                    'status' => 'pendente',
                ],
            );
        }
    }
}
