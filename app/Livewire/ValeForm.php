<?php

namespace App\Livewire;

use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\ItemVale;
use App\Models\Vale;
use App\Services\NetPriceCalculator;
use App\Services\ReservaEstoqueService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class ValeForm extends Component
{
    protected $listeners = [
        'vale-selected' => 'loadVale',
        'vale-updated' => '$refresh',
    ];

    public ?int $valeId = null;

    public ?int $clienteId = null;

    public string $observacoes = '';

    public ?int $bateriaId = null;

    public int|string $quantidade = 1;

    public bool $devolveuSucata = true;

    public string $buscaBateria = '';

    public string $observacaoItem = '';

    public ?float $previewPrecoFinal = null;

    public ?float $previewPrecoOriginal = null;

    public ?float $previewAcrescimoSucata = null;

    public function mount(): void
    {
        Gate::authorize('acesso-vendas');
    }

    public function updated(string $propertyName, NetPriceCalculator $netPriceCalculator): void
    {
        if (in_array($propertyName, ['bateriaId', 'devolveuSucata', 'quantidade'], true)) {
            $this->updatePreviewPrice($netPriceCalculator);
        }
    }

    public function loadVale(int $valeId): void
    {
        $vale = Vale::query()->with('cliente')->findOrFail($valeId);

        $this->valeId = $vale->id;
        $this->clienteId = $vale->cliente_id;
        $this->observacoes = (string) ($vale->observacoes ?? '');
    }

    public function selectBateria(int $bateriaId, NetPriceCalculator $netPriceCalculator): void
    {
        $this->bateriaId = $bateriaId;
        $this->updatePreviewPrice($netPriceCalculator);
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

        $this->reset(['bateriaId', 'quantidade', 'devolveuSucata', 'observacaoItem', 'buscaBateria']);
        $this->quantidade = 1;
        $this->devolveuSucata = true;
        $this->previewPrecoFinal = null;
        $this->previewPrecoOriginal = null;
        $this->previewAcrescimoSucata = null;

        $this->dispatch('vale-updated');
    }

    protected function updatePreviewPrice(NetPriceCalculator $netPriceCalculator): void
    {
        if (! $this->bateriaId) {
            $this->previewPrecoFinal = null;
            $this->previewPrecoOriginal = null;
            $this->previewAcrescimoSucata = null;

            return;
        }

        $bateria = Bateria::query()->find($this->bateriaId);

        if (! $bateria) {
            $this->previewPrecoFinal = null;
            $this->previewPrecoOriginal = null;
            $this->previewAcrescimoSucata = null;

            return;
        }

        $pricing = $netPriceCalculator->calculate($bateria, $this->devolveuSucata);

        $this->previewPrecoOriginal = $pricing['preco_unitario_original'];
        $this->previewPrecoFinal = $pricing['preco_unitario_final'];
        $this->previewAcrescimoSucata = $pricing['acrescimo_sucata'];
    }

    public function render(): View
    {
        $vale = $this->valeId
            ? Vale::query()->with(['itens.bateria', 'cliente'])->find($this->valeId)
            : null;

        $baterias = Bateria::query()
            ->when($this->buscaBateria !== '', function ($query): void {
                $query->where(function ($batteryQuery): void {
                    $batteryQuery->where('sku', 'like', '%'.$this->buscaBateria.'%')
                        ->orWhere('marca', 'like', '%'.$this->buscaBateria.'%');
                });
            })
            ->orderBy('sku')
            ->limit(8)
            ->get();

        return view('livewire.vale-form', [
            'clientes' => Cliente::query()->orderBy('razao_social')->get(),
            'baterias' => $baterias,
            'selectedBateria' => $this->bateriaId ? Bateria::query()->find($this->bateriaId) : null,
            'vale' => $vale,
        ]);
    }
}
