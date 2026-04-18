<?php

namespace App\Livewire;

use App\Models\RotaEntrega;
use Livewire\Component;

class MapaTaticoMonitor extends Component
{
    public $filialId;
    public $activeEntregadores = [];

    public function mount()
    {
        $this->filialId = auth()->user()->filial_id ?? session('filial_id');
        $this->loadActiveRotas();
    }

    public function loadActiveRotas()
    {
        // Pega as rotas ativas do dia para listar os entregadores que deveriam estar online
        $this->activeEntregadores = RotaEntrega::with('entregador')
            ->where('status', 'ativa')
            ->where('filial_id', $this->filialId)
            ->where('data_rota', now()->toDateString())
            ->get()
            ->map(function ($rota) {
                return [
                    'id' => $rota->entregador_id,
                    'nome' => $rota->entregador->name,
                    'last_lat' => null,
                    'last_lng' => null,
                    'last_update' => null,
                ];
            })->toArray();
    }

    public function render()
    {
        return view('livewire.mapa-tatico-monitor');
    }
}
