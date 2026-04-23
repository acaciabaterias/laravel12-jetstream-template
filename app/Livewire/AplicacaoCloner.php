<?php

namespace App\Livewire;

use App\Models\Aplicacao;
use App\Models\Veiculo;
use Livewire\Component;

class AplicacaoCloner extends Component
{
    public $destinoVeiculoId;

    public $origemVeiculoId;

    public $showModal = false;

    public $veiculosCompativeis = [];

    protected $listeners = ['openCloner'];

    public function openCloner(int $destinoId): void
    {
        $this->destinoVeiculoId = $destinoId;
        $veiculo = Veiculo::findOrFail($destinoId);

        // Fetch vehicles from the same manufacturer to allow cloning
        $this->veiculosCompativeis = Veiculo::where('fabricante_id', $veiculo->fabricante_id)
            ->where('id', '!=', $destinoId)
            ->orderBy('modelo')
            ->get();

        $this->origemVeiculoId = '';
        $this->showModal = true;
    }

    public function cloneAplicacoes(): void
    {
        $this->validate([
            'origemVeiculoId' => 'required|exists:veiculos,id',
        ]);

        $veiculoOrigem = Veiculo::with('baterias')->findOrFail($this->origemVeiculoId);
        $veiculoDestino = Veiculo::findOrFail($this->destinoVeiculoId);

        if ($veiculoOrigem->fabricante_id !== $veiculoDestino->fabricante_id) {
            $this->addError('origemVeiculoId', 'A clonagem só é permitida entre veículos do mesmo fabricante.');

            return;
        }

        $bateriasDestinoIds = $veiculoDestino->baterias()->pluck('baterias.id')->toArray();
        $clonedCount = 0;

        foreach ($veiculoOrigem->baterias as $bateria) {
            // Evitar duplicidade
            if (! in_array($bateria->id, $bateriasDestinoIds)) {
                Aplicacao::create([
                    'veiculo_id' => $veiculoDestino->id,
                    'bateria_id' => $bateria->id,
                    'observacao' => $bateria->pivot->observacao,
                ]);
                $clonedCount++;
            }
        }

        session()->flash('message', "{$clonedCount} aplicações clonadas com sucesso.");
        $this->showModal = false;

        // Notify parent to refresh
        $this->dispatch('aplicacoesUpdated');
    }

    public function render()
    {
        return view('livewire.aplicacao-cloner');
    }
}
