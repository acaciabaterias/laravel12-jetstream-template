<?php

namespace App\Livewire;

use App\Jobs\ConvertValeToOsJob;
use App\Jobs\ConvertValeToPedidoJob;
use App\Models\Vale;
use App\Services\ReservaEstoqueService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ValeConversionActions extends Component
{
    public ?int $valeId = null;

    protected $listeners = ['vale-updated' => '$refresh'];

    public function mount(): void
    {
        Gate::authorize('acesso-vendas');
    }

    public function convertToPedido(): void
    {
        Gate::authorize('acesso-vendas');

        if (! $this->valeId) {
            return;
        }

        ConvertValeToPedidoJob::dispatchSync($this->valeId, auth()->id());
        $this->dispatch('vale-updated');
    }

    public function convertToOs(): void
    {
        Gate::authorize('acesso-vendas');

        if (! $this->valeId) {
            return;
        }

        ConvertValeToOsJob::dispatchSync($this->valeId, auth()->id());
        $this->dispatch('vale-updated');
    }

    public function cancelVale(ReservaEstoqueService $reservaEstoqueService): void
    {
        Gate::authorize('acesso-vendas');

        $vale = Vale::query()->findOrFail($this->valeId);
        $reservaEstoqueService->estornarPorVale($vale);
        $vale->update(['status' => 'cancelado']);

        $this->dispatch('vale-updated');
    }

    public function render()
    {
        return view('livewire.vale-conversion-actions', [
            'vale' => $this->valeId
                ? Vale::query()->with('reservas')->find($this->valeId)
                : null,
        ]);
    }
}
