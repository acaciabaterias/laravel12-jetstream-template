<?php

namespace App\Livewire;

use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\OrdemServicoGarantia;
use App\Models\Vale;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class GarantiaManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;

    // Form fields
    public $clienteId;
    public $bateriaId;
    public $valeOriginalId;
    public $laudo;

    public function abrirGarantia()
    {
        $this->validate([
            'clienteId' => 'required|exists:clientes,id',
            'bateriaId' => 'required|exists:baterias,id',
        ]);

        OrdemServicoGarantia::create([
            'cliente_id' => $this->clienteId,
            'bateria_id' => $this->bateriaId,
            'vale_original_id' => $this->valeOriginalId ?: null,
            'filial_id' => auth()->user()->filial_id ?? session('filial_id'),
            'status' => 'aberta',
            'laudo' => $this->laudo,
        ]);

        $this->reset(['clienteId', 'bateriaId', 'valeOriginalId', 'laudo', 'showModal']);
        session()->flash('success', 'O.S. de Garantia aberta com sucesso!');
    }

    public function render()
    {
        $garantias = OrdemServicoGarantia::with(['cliente', 'bateria'])
            ->whereHas('cliente', function($q) {
                $q->where('razao_social', 'like', "%{$this->search}%")
                  ->orWhere('nome_fantasia', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.garantia-manager', [
            'garantias' => $garantias,
            'clientes' => Cliente::all(),
            'baterias' => Bateria::all(),
            'vales' => Vale::where('cliente_id', $this->clienteId)->get(),
        ]);
    }
}
