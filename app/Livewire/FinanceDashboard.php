<?php

namespace App\Livewire;

use App\Models\ConciliacaoPendente;
use App\Models\ContaBancaria;
use App\Models\TransacaoFinanceira;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class FinanceDashboard extends Component
{
    public function mount(): void
    {
        Gate::authorize('acesso-financeiro');
    }

    public function render()
    {
        return view('livewire.finance-dashboard', [
            'contas' => ContaBancaria::query()->latest('id')->limit(5)->get(),
            'transacoesPendentes' => ConciliacaoPendente::query()->where('status', 'pendente')->count(),
            'transacoesRecentes' => TransacaoFinanceira::query()->latest('data_transacao')->limit(8)->get(),
        ]);
    }
}
