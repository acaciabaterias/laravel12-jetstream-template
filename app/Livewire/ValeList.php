<?php

namespace App\Livewire;

use App\Models\Vale;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ValeList extends Component
{
    public string $status = '';

    public string $search = '';

    protected $listeners = ['vale-updated' => '$refresh'];

    public function mount(): void
    {
        Gate::authorize('acesso-vendas');
    }

    public function render()
    {
        $vales = Vale::query()
            ->with(['cliente', 'vendedor', 'itens'])
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', function ($query): void {
                $query->whereHas('cliente', function ($clienteQuery): void {
                    $clienteQuery->where('razao_social', 'like', '%'.$this->search.'%')
                        ->orWhere('nome_fantasia', 'like', '%'.$this->search.'%');
                });
            })
            ->latest('id')
            ->limit(12)
            ->get();

        return view('livewire.vale-list', [
            'vales' => $vales,
        ]);
    }
}
