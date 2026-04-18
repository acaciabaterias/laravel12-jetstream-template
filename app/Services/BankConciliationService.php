<?php

namespace App\Services;

use App\Models\ContaBancaria;
use App\Models\TransacaoFinanceira;
use Illuminate\Support\Collection;

class BankConciliationService
{
    /**
     * Simula a busca de extrato de uma API bancária (Open Finance).
     */
    public function fetchExternalBankStatement(ContaBancaria $conta): Collection
    {
        // Mock de dados externos vindos do banco
        return collect([
            ['id_extrato' => 'TX_123', 'valor' => 500.00, 'data' => now()->format('Y-m-d'), 'descricao' => 'PIX RECEBIDO VALE #1'],
            ['id_extrato' => 'TX_124', 'valor' => 120.50, 'data' => now()->format('Y-m-d'), 'descricao' => 'PAGTO TESTE'],
            ['id_extrato' => 'TX_125', 'valor' => -1000.00, 'data' => now()->subDay()->format('Y-m-d'), 'descricao' => 'PGTO FORNECEDOR MOURA'],
        ]);
    }

    /**
     * Executa o Motor de Conciliação (FR-FIN-01).
     */
    public function conciliarConta(ContaBancaria $conta): array
    {
        $externalData = $this->fetchExternalBankStatement($conta);
        $pendentes = TransacaoFinanceira::where('conta_id', $conta->id)
            ->where('status', 'pendente')
            ->get();

        $matches = 0;
        $ignored = 0;

        foreach ($externalData as $ext) {
            // Lógica de Match Simples: Valor exato + Data aproximada (US01)
            $match = $pendentes->filter(function ($t) use ($ext) {
                return abs($t->valor) == abs($ext['valor'])
                    && $t->data->format('Y-m-d') == $ext['data'];
            })->first();

            if ($match) {
                $match->update([
                    'status' => 'conciliado',
                    'origem' => $match->origem.' (Conciliado ID: '.$ext['id_extrato'].')',
                ]);
                $matches++;
            } else {
                $ignored++;
            }
        }

        return [
            'processados' => $externalData->count(),
            'conciliados' => $matches,
            'pendentes_averiguacao' => $ignored,
        ];
    }
}
