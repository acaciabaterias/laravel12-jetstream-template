<?php

namespace App\Livewire;

use App\Models\Bateria;
use App\Models\ContaSucataMovimentacao;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ContaSucataDashboard extends Component
{
    public ?int $bateriaId = null;

    public string $tipoMovimento = 'credito';

    public string $quantidadeKg = '1';

    public string $valorUnitario = '0';

    public string $origem = 'ajuste_manual';

    public function mount(): void
    {
        Gate::authorize('acesso-estoque');
    }

    public function registrarMovimento(): void
    {
        Gate::authorize('acesso-estoque');

        $validated = $this->validate([
            'bateriaId' => ['required', 'exists:baterias,id'],
            'tipoMovimento' => ['required', 'in:credito,debito'],
            'quantidadeKg' => ['required', 'numeric', 'min:0.01'],
            'valorUnitario' => ['required', 'numeric', 'min:0'],
            'origem' => ['required', 'string', 'max:100'],
        ]);

        $valorMovimento = round((float) $validated['quantidadeKg'] * (float) $validated['valorUnitario'], 2);
        $saldoAnterior = (float) (ContaSucataMovimentacao::query()->latest('id')->value('saldo_resultante') ?? 0);
        $saldoResultante = $validated['tipoMovimento'] === 'credito'
            ? $saldoAnterior + $valorMovimento
            : $saldoAnterior - $valorMovimento;

        ContaSucataMovimentacao::query()->create([
            'entidade_tipo' => Bateria::class,
            'entidade_id' => $validated['bateriaId'],
            'tipo_movimento' => $validated['tipoMovimento'],
            'quantidade_kg' => $validated['quantidadeKg'],
            'valor_unitario' => $validated['valorUnitario'],
            'saldo_resultante' => $saldoResultante,
            'origem' => $validated['origem'],
        ]);

        $this->reset(['bateriaId']);
        $this->tipoMovimento = 'credito';
        $this->quantidadeKg = '1';
        $this->valorUnitario = '0';
        $this->origem = 'ajuste_manual';

        $this->dispatch('inventory-updated');
    }

    public function render()
    {
        $movimentacoes = ContaSucataMovimentacao::query()
            ->latest('id')
            ->limit(6)
            ->get();

        return view('livewire.conta-sucata-dashboard', [
            'baterias' => Bateria::query()->orderBy('sku')->get(),
            'movimentacoes' => $movimentacoes,
            'saldoAtual' => (float) ($movimentacoes->first()?->saldo_resultante ?? 0),
        ]);
    }
}
