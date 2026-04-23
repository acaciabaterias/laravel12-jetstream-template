<?php

namespace App\Livewire;

use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\ItemVale;
use App\Models\Vale;
use App\Services\NetPriceCalculator;
use App\Services\ReservaEstoqueService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ValeForm extends Component
{
    public ?int $valeId = null;

    public ?int $clienteId = null;

    public string $observacoes = '';

    public ?int $bateriaId = null;

    public int|string $quantidade = 1;

    public bool $devolveuSucata = true;

    public string $observacaoItem = '';

    public ?float $previewPrecoFinal = null;

    public function mount(): void
    {
        Gate::authorize('acesso-vendas');
    }

    public function updated($propertyName, NetPriceCalculator $netPriceCalculator): void
    {
        if (in_array($propertyName, ['bateriaId', 'devolveuSucata'], true) && $this->bateriaId) {
            $bateria = Bateria::query()->find($this->bateriaId);

            if ($bateria) {
                $this->previewPrecoFinal = $netPriceCalculator->calculate($bateria, $this->devolveuSucata)['preco_unitario_final'];
            }
        }
    }

    public function createVale(): void
    {
        Gate::authorize('acesso-vendas');

        $validated = $this->validate([
            'clienteId' => ['required', 'exists:clientes,id'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ]);

        $vale = Vale::query()->create([
            'cliente_id' => $validated['clienteId'],
            'vendedor_id' => auth()->id(),
            'status' => 'aberto',
            'data_criacao' => now(),
            'observacoes' => $validated['observacoes'],
            'created_by' => auth()->id(),
        ]);

        $this->valeId = $vale->id;
    }

    public function addItem(NetPriceCalculator $netPriceCalculator, ReservaEstoqueService $reservaEstoqueService): void
    {
        Gate::authorize('acesso-vendas');

        if (! $this->valeId) {
            $this->addError('valeId', 'Crie o vale antes de adicionar itens.');

            return;
        }

        $validated = $this->validate([
            'bateriaId' => ['required', 'exists:baterias,id'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'devolveuSucata' => ['required', 'boolean'],
            'observacaoItem' => ['nullable', 'string', 'max:1000'],
        ]);

        $vale = Vale::query()->findOrFail($this->valeId);
        $bateria = Bateria::query()->findOrFail($validated['bateriaId']);
        $pricing = $netPriceCalculator->calculate($bateria, $validated['devolveuSucata']);

        $itemVale = ItemVale::query()->create([
            'vale_id' => $vale->id,
            'bateria_id' => $bateria->id,
            'quantidade' => $validated['quantidade'],
            'preco_unitario_original' => $pricing['preco_unitario_original'],
            'preco_unitario_final' => $pricing['preco_unitario_final'],
            'flag_devolveu_sucata' => $validated['devolveuSucata'],
            'observacao' => $validated['observacaoItem'],
        ]);

        $reservaEstoqueService->reservar($vale, $itemVale, auth()->user());

        $this->reset(['bateriaId', 'quantidade', 'devolveuSucata', 'observacaoItem']);
        $this->quantidade = 1;
        $this->devolveuSucata = true;
        $this->previewPrecoFinal = null;

        $this->dispatch('vale-updated');
    }

    public function render()
    {
        $vale = $this->valeId
            ? Vale::query()->with(['itens.bateria', 'cliente'])->find($this->valeId)
            : null;

        return view('livewire.vale-form', [
            'clientes' => Cliente::query()->orderBy('razao_social')->get(),
            'baterias' => Bateria::query()->orderBy('sku')->get(),
            'vale' => $vale,
        ]);
    }
}
