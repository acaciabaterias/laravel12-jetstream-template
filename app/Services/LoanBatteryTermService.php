<?php

namespace App\Services;

use App\Models\BateriaEmprestimo;

class LoanBatteryTermService
{
    public function generate(BateriaEmprestimo $bateriaEmprestimo): string
    {
        $ordemServico = $bateriaEmprestimo->ordemServicoGarantia()->with(['cliente', 'bateria'])->firstOrFail();

        return implode("\n", [
            'TERMO DE EMPRESTIMO DE BATERIA',
            'Cliente: '.$ordemServico->cliente->razao_social,
            'Produto em analise: '.$ordemServico->bateria->sku,
            'Bateria provisoria: '.$bateriaEmprestimo->bateriaUsada->sku,
            'Devolucao prevista: '.$bateriaEmprestimo->data_devolucao_prevista?->format('d/m/Y H:i'),
        ]);
    }
}
