<?php

namespace App\Livewire;

use App\Models\Deposito;
use App\Models\EstoqueSaldo;
use Livewire\Component;
use Livewire\WithPagination;

class EstoqueManager extends Component
{
    use WithPagination;

    public $filtroDeposito = '';
    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroDeposito()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = EstoqueSaldo::with(['bateria', 'deposito']);

        if ($this->filtroDeposito) {
            $query->where('deposito_id', $this->filtroDeposito);
        }

        if ($this->search) {
            $query->whereHas('bateria', function ($q) {
                $q->where('sku', 'like', '%' . $this->search . '%')
                  ->orWhere('marca', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.estoque-manager', [
            'saldos' => $query->paginate(15),
            'depositos' => Deposito::all(),
        ]);
    }
}
