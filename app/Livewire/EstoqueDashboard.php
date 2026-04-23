<?php

namespace App\Livewire;

use App\Models\EstoqueSaldo;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class EstoqueDashboard extends Component
{
    public string $filtroBusca = '';

    public function mount(): void
    {
        Gate::authorize('acesso-estoque');
    }

    public function render()
    {
        $saldos = EstoqueSaldo::query()
            ->with(['bateria', 'deposito'])
            ->when($this->filtroBusca !== '', function ($query): void {
                $query->whereHas('bateria', function ($batteryQuery): void {
                    $batteryQuery->where('sku', 'like', '%'.$this->filtroBusca.'%')
                        ->orWhere('marca', 'like', '%'.$this->filtroBusca.'%');
                });
            })
            ->orderByDesc('quantidade_atual')
            ->get();

        return view('livewire.estoque-dashboard', [
            'saldos' => $saldos,
            'saldoTotal' => (int) $saldos->sum('quantidade_atual'),
        ]);
    }
}
