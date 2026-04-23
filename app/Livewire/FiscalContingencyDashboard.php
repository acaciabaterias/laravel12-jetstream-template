<?php

namespace App\Livewire;

use App\Models\FilaContingencia;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class FiscalContingencyDashboard extends Component
{
    public function mount(): void
    {
        Gate::authorize('acesso-financeiro');
    }

    public function render()
    {
        return view('livewire.fiscal-contingency-dashboard', [
            'contingencias' => FilaContingencia::query()->latest('id')->limit(10)->get(),
            'criticas' => FilaContingencia::query()->where('tentativas', '>=', 3)->count(),
        ]);
    }
}
