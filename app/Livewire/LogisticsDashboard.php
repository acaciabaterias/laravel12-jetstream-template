<?php

namespace App\Livewire;

use App\Models\GeolocalizacaoEvento;
use App\Models\RotaEntrega;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class LogisticsDashboard extends Component
{
    protected $listeners = ['route-updated' => '$refresh'];

    public function mount(): void
    {
        Gate::authorize('acesso-logistica');
    }

    public function render()
    {
        return view('livewire.logistics-dashboard', [
            'rotasAtivas' => RotaEntrega::query()
                ->with(['entregador', 'pontos'])
                ->whereIn('status', ['planejada', 'em_rota'])
                ->latest('id')
                ->limit(8)
                ->get(),
            'eventosRecentes' => GeolocalizacaoEvento::query()
                ->latest('recorded_at')
                ->limit(8)
                ->get(),
        ]);
    }
}
