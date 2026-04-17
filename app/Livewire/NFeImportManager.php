<?php

namespace App\Livewire;

use App\Jobs\ImportNFeJob;
use App\Models\Bateria;
use App\Models\Deposito;
use App\Services\NFeParserService;
use Livewire\Component;
use Livewire\WithFileUploads;

class NFeImportManager extends Component
{
    use WithFileUploads;

    public $xmlFile;
    public $deposito_id;

    public $parsedData = null;
    public $mappedItems = [];
    public $bateriasInternas = [];
    
    public $isProcessing = false;

    // To prevent processing multiple times
    protected $rules = [
        'deposito_id' => 'required|exists:depositos,id',
        'xmlFile' => 'required|max:10240', // 10MB Limit
    ];

    public function mount()
    {
        $this->bateriasInternas = Bateria::orderBy('sku')->get()->mapWithKeys(function ($b) {
            return [$b->id => $b->sku . ' - ' . $b->marca];
        })->toArray();
    }

    public function processarXML(NFeParserService $parser)
    {
        $this->validateOnly('xmlFile');

        if (!$this->xmlFile) return;

        try {
            $content = $this->xmlFile->get();
            $this->parsedData = $parser->parse($content);
            $this->isProcessing = true;

            // Auto-try to map by trying to find exact barcode (EAN) or matching SKU with Supplier Code
            foreach ($this->parsedData['itens'] as $index => $item) {
                $matchedBateria = null;

                if (!empty($item['ean'])) {
                    // Mocks typical matching logic, currently using SKU or we could use specific EAN columns if present later
                    $matchedBateria = Bateria::where('sku', $item['ean'])->first();
                }

                $this->mappedItems[$index] = [
                    'original' => $item,
                    'bateria_id' => $matchedBateria ? $matchedBateria->id : '',
                    'quantidade' => $item['quantidade'], // Allow overriding quantity
                ];
            }
            
            $this->reset('xmlFile');
        } catch (\Exception $e) {
            $this->addError('xmlFile', $e->getMessage());
        }
    }

    public function finalizarImportacao()
    {
        $this->validateOnly('deposito_id');

        if (empty($this->parsedData)) {
            $this->addError('geral', 'Processe um XML primeiro.');
            return;
        }

        $allMapped = true;
        foreach ($this->mappedItems as $mapped) {
            if (empty($mapped['bateria_id'])) {
                $allMapped = false;
                break;
            }
        }

        if (!$allMapped) {
            $this->addError('mapeamento', 'Por favor, vincule todos os itens da nota a um produto interno (ou remova-os definindo a quantidade para 0).');
            return;
        }

        // Dispatch Job bypassing queue locally for MVP visibility or Queue it normally
        ImportNFeJob::dispatchSync(
            auth()->user()->filial_id ?? session('filial_id'),
            $this->deposito_id,
            auth()->id(),
            $this->parsedData['chave'],
            $this->parsedData['fornecedor'],
            $this->mappedItems
        );

        session()->flash('message', 'Importação concluída e estoque alimentado com sucesso!');
        return redirect()->route('dashboard'); // Replace with where you want to redirect
    }

    public function cancel()
    {
        $this->parsedData = null;
        $this->mappedItems = [];
        $this->isProcessing = false;
        $this->reset('xmlFile');
    }

    public function render()
    {
        return view('livewire.n-fe-import-manager', [
            'depositos' => Deposito::all()
        ]);
    }
}
