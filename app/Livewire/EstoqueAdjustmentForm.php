<?php

namespace App\Livewire;

use App\Models\Bateria;
use App\Models\Deposito;
use App\Services\EstoqueSaldoService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class EstoqueAdjustmentForm extends Component
{
    public ?int $bateriaId = null;

    public ?int $depositoId = null;

    public string $tipoOperacao = 'entrada';

    public int|string $quantidade = 1;

    public string $origem = 'ajuste_manual';

    public string $justificativa = '';

    public function mount(): void
    {
        Gate::authorize('acesso-estoque');
    }

    public function salvar(EstoqueSaldoService $estoqueSaldoService): void
    {
        Gate::authorize('acesso-estoque');

        $validated = $this->validate([
            'bateriaId' => ['required', 'exists:baterias,id'],
            'depositoId' => ['required', 'exists:depositos,id'],
            'tipoOperacao' => ['required', 'in:entrada,saida,ajuste_positivo,ajuste_negativo'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'origem' => ['required', 'string', 'max:100'],
            'justificativa' => ['required_if:origem,ajuste_manual', 'nullable', 'string', 'max:1000'],
        ], [
            'bateriaId.required' => 'Selecione uma bateria.',
            'depositoId.required' => 'Selecione um deposito.',
            'justificativa.required_if' => 'Informe a justificativa para ajuste manual.',
        ]);

        $estoqueSaldoService->registrarMovimentacao(
            bateria: Bateria::query()->findOrFail($validated['bateriaId']),
            deposito: Deposito::query()->findOrFail($validated['depositoId']),
            quantidade: (int) $validated['quantidade'],
            tipoOperacao: $validated['tipoOperacao'],
            user: auth()->user(),
            origem: $validated['origem'],
            justificativa: $validated['justificativa'] ?: null,
        );

        $this->reset(['bateriaId', 'depositoId', 'justificativa']);
        $this->tipoOperacao = 'entrada';
        $this->quantidade = 1;
        $this->origem = 'ajuste_manual';

        $this->dispatch('inventory-updated');
    }

    public function render()
    {
        return view('livewire.estoque-adjustment-form', [
            'baterias' => Bateria::query()->orderBy('sku')->get(),
            'depositos' => Deposito::query()->orderBy('nome')->get(),
        ]);
    }
}
