<?php

namespace App\Livewire;

use App\Models\OrdemServicoGarantia;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class GarantiaBoard extends Component
{
    protected $listeners = ['garantia-updated' => '$refresh'];

    public function mount(): void
    {
        Gate::authorize('acesso-tecnico');
    }

    public function render()
    {
        return view('livewire.garantia-board', [
            'garantias' => OrdemServicoGarantia::query()
                ->with(['cliente', 'bateria'])
                ->latest('id')
                ->limit(10)
                ->get(),
        ]);
    }
}
