<?php

namespace App\Livewire;

use App\Models\Aplicacao;
use App\Models\Bateria;
use App\Models\Veiculo;
use Livewire\Component;

class ApplicationManager extends Component
{
    public int $vehicleId;

    public string $searchBateria = '';

    public array $bateriasResults = [];

    public ?int $bateriaSelecionadaId = null;

    public string $observacao = '';

    public function mount(int $vehicleId): void
    {
        $this->vehicleId = $vehicleId;
    }

    public function updatedSearchBateria(): void
    {
        if (strlen($this->searchBateria) < 2) {
            $this->bateriasResults = [];

            return;
        }

        $this->bateriasResults = Bateria::query()
            ->where(function ($query): void {
                $query->where('sku', 'like', '%'.$this->searchBateria.'%')
                    ->orWhere('marca', 'like', '%'.$this->searchBateria.'%');
            })
            ->limit(8)
            ->get()
            ->map(fn (Bateria $bateria): array => [
                'id' => $bateria->id,
                'sku' => $bateria->sku,
                'marca' => $bateria->marca,
            ])
            ->all();
    }

    public function selectBateria(int $bateriaId): void
    {
        $this->bateriaSelecionadaId = $bateriaId;

        $bateria = Bateria::query()->find($bateriaId);
        $this->searchBateria = $bateria ? "{$bateria->sku} - {$bateria->marca}" : '';
        $this->bateriasResults = [];
    }

    public function addAplicacao(): void
    {
        $this->validate([
            'bateriaSelecionadaId' => ['required', 'exists:baterias,id'],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ]);

        $exists = Aplicacao::query()
            ->where('veiculo_id', $this->vehicleId)
            ->where('bateria_id', $this->bateriaSelecionadaId)
            ->exists();

        if ($exists) {
            $this->addError('bateriaSelecionadaId', 'Esta bateria já está vinculada ao veículo.');

            return;
        }

        Aplicacao::query()->create([
            'veiculo_id' => $this->vehicleId,
            'bateria_id' => $this->bateriaSelecionadaId,
            'observacao' => $this->observacao ?: null,
        ]);

        $this->reset(['bateriaSelecionadaId', 'searchBateria', 'observacao']);
        $this->bateriasResults = [];
    }

    public function removeAplicacao(int $aplicacaoId): void
    {
        $aplicacao = Aplicacao::query()->findOrFail($aplicacaoId);
        $aplicacao->delete();
    }

    public function render()
    {
        $veiculo = Veiculo::query()->with('baterias')->findOrFail($this->vehicleId);

        $aplicacoes = Aplicacao::query()
            ->with('bateria')
            ->where('veiculo_id', $this->vehicleId)
            ->latest('id')
            ->get();

        return view('livewire.application-manager', [
            'veiculo' => $veiculo,
            'aplicacoes' => $aplicacoes,
        ]);
    }
}
