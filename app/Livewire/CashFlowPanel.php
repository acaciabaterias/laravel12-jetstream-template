<?php

namespace App\Livewire;

use App\Models\FluxoCaixaProjetado;
use App\Models\OrdemServicoGarantia;
use App\Models\PedidoVenda;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class CashFlowPanel extends Component
{
    public function mount(): void
    {
        Gate::authorize('acesso-financeiro');
    }

    public function refreshProjection(): void
    {
        Gate::authorize('acesso-financeiro');

        $saldoInicial = (float) \App\Models\TransacaoFinanceira::query()->sum('valor');
        $totalReceber = (float) PedidoVenda::query()->where('status', 'faturado')->sum('valor_total');
        $totalPagar = (float) OrdemServicoGarantia::query()->where('resultado', 'improcedente')->sum('cobranca_valor');

        FluxoCaixaProjetado::query()->updateOrCreate(
            ['data_referencia' => now()->toDateString()],
            [
                'saldo_inicial' => $saldoInicial,
                'total_receber' => $totalReceber,
                'total_pagar' => $totalPagar,
                'saldo_projetado' => $saldoInicial + $totalReceber - $totalPagar,
            ],
        );
    }

    public function render()
    {
        return view('livewire.cash-flow-panel', [
            'projecoes' => FluxoCaixaProjetado::query()->latest('data_referencia')->limit(7)->get(),
        ]);
    }
}
