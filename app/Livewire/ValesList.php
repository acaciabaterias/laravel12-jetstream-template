<?php

namespace App\Livewire;

use App\Models\Vale;
use Livewire\Component;
use Livewire\WithPagination;

class ValesList extends Component
{
    use WithPagination;

    public string $searchCliente = '';

    public string $filterStatus = '';

    public string $filterVendedor = '';

    public string $dataDe = '';

    public string $dataAte = '';

    protected $queryString = [
        'searchCliente' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'dataDe' => ['except' => ''],
        'dataAte' => ['except' => ''],
    ];

    public function updatingSearchCliente(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Vale::with(['cliente', 'vendedor', 'itens'])
            ->when($this->searchCliente, fn ($q) => $q->whereHas('cliente', fn ($c) => $c->where('nome', 'like', '%'.$this->searchCliente.'%')))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterVendedor, fn ($q) => $q->where('vendedor_id', $this->filterVendedor))
            ->when($this->dataDe, fn ($q) => $q->whereDate('created_at', '>=', $this->dataDe))
            ->when($this->dataAte, fn ($q) => $q->whereDate('created_at', '<=', $this->dataAte))
            ->latest()
            ->paginate(20);

        return view('livewire.vales-list', ['vales' => $query]);
    }
}
