<?php

namespace App\Livewire;

use App\Models\OrdemServico;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class OrdemServicoForm extends Component
{
    public ?int $ordemServicoId = null;

    public string $laudo = '';

    public string $observacoes = '';

    public function loadOrder(int $ordemServicoId): void
    {
        Gate::authorize('acesso-tecnico');

        $ordemServico = OrdemServico::query()->findOrFail($ordemServicoId);
        $this->ordemServicoId = $ordemServico->id;
        $this->laudo = $ordemServico->laudo ?? '';
        $this->observacoes = $ordemServico->observacoes ?? '';
    }

    public function save(): void
    {
        Gate::authorize('acesso-tecnico');

        $validated = $this->validate([
            'ordemServicoId' => ['required', 'exists:ordens_servico,id'],
            'laudo' => ['nullable', 'string', 'max:2000'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        OrdemServico::query()->findOrFail($validated['ordemServicoId'])->update([
            'laudo' => $validated['laudo'],
            'observacoes' => $validated['observacoes'],
        ]);
    }

    public function render()
    {
        return view('livewire.ordem-servico-form', [
            'ordensServico' => OrdemServico::query()->latest('id')->limit(6)->get(),
        ]);
    }
}
