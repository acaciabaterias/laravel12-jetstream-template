<?php

namespace App\Livewire;

use App\Models\Fabricante;
use Livewire\Component;
use Livewire\WithPagination;

class FabricanteManager extends Component
{
    use WithPagination;

    public $nome;

    public $codigo;

    public $fabricanteId;

    public $isEditMode = false;

    public $showModal = false;

    public $search = '';

    protected $rules = [
        'nome' => 'required|string|max:255',
        'codigo' => 'nullable|string|max:50',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $fabricante = Fabricante::withTrashed()->findOrFail($id);
        $this->fabricanteId = $fabricante->id;
        $this->nome = $fabricante->nome;
        $this->codigo = $fabricante->codigo;

        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function store(): void
    {
        $this->validate();

        $data = [
            'nome' => $this->nome,
            'codigo' => $this->codigo,
        ];

        if ($this->isEditMode) {
            $fabricante = Fabricante::withTrashed()->findOrFail($this->fabricanteId);
            $fabricante->update($data);
        } else {
            Fabricante::create($data);
        }

        $this->showModal = false;
        $this->resetInputFields();
    }

    public function toggleStatus(int $id): void
    {
        $fabricante = Fabricante::withTrashed()->findOrFail($id);
        if ($fabricante->trashed()) {
            $fabricante->restore();
        } else {
            $fabricante->delete();
        }
    }

    private function resetInputFields(): void
    {
        $this->nome = '';
        $this->codigo = '';
        $this->fabricanteId = null;
    }

    public function render()
    {
        $query = Fabricante::withTrashed();

        if (! empty($this->search)) {
            $query->where('nome', 'like', '%'.$this->search.'%')
                ->orWhere('codigo', 'like', '%'.$this->search.'%');
        }

        return view('livewire.fabricante-manager', [
            'fabricantes' => $query->latest()->paginate(10),
        ]);
    }
}
