<?php

namespace App\Livewire;

use App\Models\Bateria;
use App\Models\Fabricante;
use App\Models\Veiculo;
use Livewire\Component;
use Livewire\WithPagination;

class VeiculoManager extends Component
{
    use WithPagination;

    // Vehicle fields
    public $fabricante_id;

    public $modelo;

    public $motorizacao;

    public $ano_inicio;

    public $ano_fim;

    public $atributos_dinamicos = '';

    // UI State
    public $veiculoId;

    public $isEditMode = false;

    public $showModal = false;

    public $search = '';

    public $fabricanteFilter = '';

    public $anoFilter = '';

    public $currentTab = 'basico'; // basico | aplicacoes

    // Applications State
    public $aplicacoes = []; // [ ['bateria_id' => 1, 'sku'=> '...', 'observacao' => '...'], ... ]

    public $searchBateria = '';

    public $bateriasResults = [];

    public $bateriaSelecionada = null;

    public $novaObservacao = '';

    protected function rules()
    {
        return [
            'fabricante_id' => 'required|exists:fabricantes,id',
            'modelo' => 'required|string|max:255',
            'motorizacao' => 'nullable|string|max:255',
            'ano_inicio' => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
            'ano_fim' => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
            'atributos_dinamicos' => 'nullable|string',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFabricanteFilter(): void
    {
        $this->resetPage();
    }

    public function updatingAnoFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearchBateria(): void
    {
        if (strlen($this->searchBateria) >= 2) {
            $this->bateriasResults = Bateria::where('sku', 'like', '%'.$this->searchBateria.'%')
                ->orWhere('marca', 'like', '%'.$this->searchBateria.'%')
                ->take(5)->get();
        } else {
            $this->bateriasResults = [];
        }
    }

    public function selectBateria(int $bateriaId): void
    {
        $this->bateriaSelecionada = Bateria::find($bateriaId);
        $this->bateriasResults = [];
        $this->searchBateria = $this->bateriaSelecionada->sku.' - '.$this->bateriaSelecionada->marca;
    }

    public function addAplicacao(): void
    {
        if (! $this->bateriaSelecionada) {
            return;
        }

        // Check for duplicates
        foreach ($this->aplicacoes as $app) {
            if ($app['bateria_id'] === $this->bateriaSelecionada->id) {
                $this->addError('bateria_duplicada', 'Esta bateria já está vinculada.');

                return;
            }
        }

        $this->aplicacoes[] = [
            'bateria_id' => $this->bateriaSelecionada->id,
            'sku' => $this->bateriaSelecionada->sku,
            'marca' => $this->bateriaSelecionada->marca,
            'tecnologia' => $this->bateriaSelecionada->tecnologia,
            'observacao' => $this->novaObservacao,
        ];

        $this->bateriaSelecionada = null;
        $this->searchBateria = '';
        $this->novaObservacao = '';
    }

    public function removeAplicacao(int $index): void
    {
        unset($this->aplicacoes[$index]);
        $this->aplicacoes = array_values($this->aplicacoes); // Re-index
    }

    public function create(): void
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
        $this->currentTab = 'basico';
    }

    public function edit(int $id): void
    {
        $veiculo = Veiculo::with('baterias')->withTrashed()->findOrFail($id);
        $this->veiculoId = $veiculo->id;
        $this->fabricante_id = $veiculo->fabricante_id;
        $this->modelo = $veiculo->modelo;
        $this->motorizacao = $veiculo->motorizacao;
        $this->ano_inicio = $veiculo->ano_inicio;
        $this->ano_fim = $veiculo->ano_fim;
        $this->atributos_dinamicos = $veiculo->atributos_dinamicos ? json_encode($veiculo->atributos_dinamicos, JSON_PRETTY_PRINT) : '';

        $this->aplicacoes = $veiculo->baterias->map(function ($bateria) {
            return [
                'bateria_id' => $bateria->id,
                'sku' => $bateria->sku,
                'marca' => $bateria->marca,
                'tecnologia' => $bateria->tecnologia,
                'observacao' => $bateria->pivot->observacao,
            ];
        })->toArray();

        $this->isEditMode = true;
        $this->showModal = true;
        $this->currentTab = 'basico';
    }

    public function store(): void
    {
        $this->validate();

        $atributos = null;
        if (! empty($this->atributos_dinamicos)) {
            $atributos = json_decode($this->atributos_dinamicos, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError('atributos_dinamicos', 'Formato JSON inválido.');
                $this->currentTab = 'basico';

                return;
            }
        }

        $data = [
            'fabricante_id' => $this->fabricante_id,
            'modelo' => $this->modelo,
            'motorizacao' => $this->motorizacao,
            'ano_inicio' => $this->ano_inicio,
            'ano_fim' => $this->ano_fim,
            'atributos_dinamicos' => $atributos,
        ];

        if ($this->isEditMode) {
            $veiculo = Veiculo::withTrashed()->findOrFail($this->veiculoId);
            $veiculo->update($data);
        } else {
            $veiculo = Veiculo::create($data);
        }

        // Sync Applications (Baterias)
        $syncData = [];
        foreach ($this->aplicacoes as $app) {
            $syncData[$app['bateria_id']] = [
                'observacao' => $app['observacao'],
            ];
        }
        $veiculo->baterias()->sync($syncData);

        $this->showModal = false;
        $this->resetInputFields();
    }

    public function toggleStatus(int $id): void
    {
        $veiculo = Veiculo::withTrashed()->findOrFail($id);
        if ($veiculo->trashed()) {
            $veiculo->restore();
        } else {
            $veiculo->delete();
        }
    }

    public function setTab(string $tabName): void
    {
        $this->currentTab = $tabName;
    }

    private function resetInputFields(): void
    {
        $this->veiculoId = null;
        $this->fabricante_id = '';
        $this->modelo = '';
        $this->motorizacao = '';
        $this->ano_inicio = null;
        $this->ano_fim = null;
        $this->atributos_dinamicos = '';
        $this->aplicacoes = [];
        $this->searchBateria = '';
        $this->bateriasResults = [];
        $this->bateriaSelecionada = null;
        $this->novaObservacao = '';
    }

    public function render()
    {
        $query = Veiculo::with('fabricante')->withTrashed();

        if (! empty($this->search)) {
            $query->where('modelo', 'like', '%'.$this->search.'%')
                ->orWhere('motorizacao', 'like', '%'.$this->search.'%');
        }

        if (! empty($this->fabricanteFilter)) {
            $query->where('fabricante_id', $this->fabricanteFilter);
        }

        if (! empty($this->anoFilter) && is_numeric($this->anoFilter)) {
            $ano = (int) $this->anoFilter;
            $query->where(function ($yearQuery) use ($ano): void {
                $yearQuery
                    ->where(function ($openEnded) use ($ano): void {
                        $openEnded->whereNotNull('ano_inicio')
                            ->where('ano_inicio', '<=', $ano)
                            ->whereNull('ano_fim');
                    })
                    ->orWhere(function ($range) use ($ano): void {
                        $range->whereNotNull('ano_inicio')
                            ->whereNotNull('ano_fim')
                            ->where('ano_inicio', '<=', $ano)
                            ->where('ano_fim', '>=', $ano);
                    })
                    ->orWhere('ano_inicio', $ano)
                    ->orWhere('ano_fim', $ano);
            });
        }

        return view('livewire.veiculo-manager', [
            'veiculos' => $query->latest()->paginate(10),
            'fabricantes' => Fabricante::orderBy('nome')->get(),
        ]);
    }
}
