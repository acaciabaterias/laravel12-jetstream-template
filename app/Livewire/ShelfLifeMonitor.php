<?php

namespace App\Livewire;

use App\Models\EstoqueMovimentacao;
use App\Models\EstoqueSaldo;
use Livewire\Component;
use Livewire\WithPagination;

class ShelfLifeMonitor extends Component
{
    use WithPagination;

    public $diasLimite = 90;

    public function updatedDiasLimite()
    {
        $this->resetPage();
    }

    public function render()
    {
        $limiteDate = now()->subDays($this->diasLimite);

        // Optimized approach for MVP: get all items in stock and map their last 'entrada' date
        $saldos = EstoqueSaldo::with(['bateria', 'deposito'])
            ->where('quantidade_atual', '>', 0)
            ->get()
            ->map(function ($saldo) use ($limiteDate) {
                // Find the latest "entrada" for this specific battery in this branch
                $ultimaEntrada = EstoqueMovimentacao::where('bateria_id', $saldo->bateria_id)
                    ->where('filial_id', $saldo->filial_id)
                    ->where('tipo', 'entrada')
                    ->latest('data')
                    ->first();

                if ($ultimaEntrada && $ultimaEntrada->data < $limiteDate) {
                    $saldo->dias_estagnado = now()->diffInDays($ultimaEntrada->data);
                    $saldo->data_ultima_entrada = $ultimaEntrada->data;
                    return $saldo;
                }
                return null;
            })
            ->filter()
            ->sortByDesc('dias_estagnado');

        return view('livewire.shelf-life-monitor', [
            'alertas' => $saldos,
        ]);
    }
}
