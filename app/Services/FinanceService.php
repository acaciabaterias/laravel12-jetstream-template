<?php

namespace App\Services;

use App\Models\Filial;
use App\Models\TransacaoFinanceira;
use Carbon\Carbon;
use Exception;

class FinanceService
{
    /**
     * Registra uma nova transação no ledger e valida trava contábil.
     */
    public function registrar(array $data): TransacaoFinanceira
    {
        $dataRef = Carbon::parse($data['data']);
        $this->validarTravaContabil($data['filial_id'] ?? null, $dataRef);

        return TransacaoFinanceira::create($data);
    }

    /**
     * Valida se a data da transação não está antes do fechamento contábil (FR-FIN-03).
     */
    public function validarTravaContabil($filialId, Carbon $data): void
    {
        if (! $filialId) {
            return;
        }

        $filial = Filial::find($filialId);
        if ($filial && $filial->data_fechamento_contabil) {
            if ($data->lessThanOrEqualTo($filial->data_fechamento_contabil)) {
                throw new Exception('Operação bloqueada: O mês contábil até '.$filial->data_fechamento_contabil->format('d/m/Y').' já foi fechado para este CNPJ.');
            }
        }
    }

    /**
     * Calcula o saldo projetado consolidando receitas e despesas pendentes.
     */
    public function projetarSaldoDiario($filialId, Carbon $ateData): array
    {
        $transacoes = TransacaoFinanceira::whereHas('conta', function ($q) use ($filialId) {
            $q->where('filial_id', $filialId);
        })
            ->where('data', '<=', $ateData)
            ->where('status', '!=', 'cancelado')
            ->get();

        $receber = $transacoes->where('tipo', 'receita')->where('status', 'pendente')->sum('valor');
        $pagar = $transacoes->where('tipo', 'despesa')->where('status', 'pendente')->sum('valor');
        $realizado = $transacoes->where('status', '!=', 'pendente')->sum(function ($t) {
            return $t->tipo === 'receita' ? $t->valor : -$t->valor;
        });

        return [
            'total_receber' => $receber,
            'total_pagar' => $pagar,
            'saldo_projetado' => $realizado + $receber - $pagar,
        ];
    }
}
