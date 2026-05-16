<?php

namespace App\Livewire;

use App\Jobs\ProcessXmlImportJob;
use App\Models\XmlImportacao;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class XmlImportForm extends Component
{
    public string $chaveNfe = '';

    public string $payloadXml = '';

    public function mount(): void
    {
        Gate::authorize('acesso-estoque');
    }

    public function importar(): void
    {
        Gate::authorize('acesso-estoque');

        $validated = $this->validate([
            'chaveNfe' => ['required', 'string', 'size:44', Rule::unique('xml_importacoes', 'chave_nfe')],
            'payloadXml' => ['required', 'string'],
        ], [
            'chaveNfe.unique' => 'Esta chave NFe ja foi importada.',
        ]);

        $importacao = XmlImportacao::query()->create([
            'chave_nfe' => $validated['chaveNfe'],
            'status' => 'pendente',
            'log_erros' => null,
            'payload_xml' => [
                'raw' => $validated['payloadXml'],
            ],
        ]);

        ProcessXmlImportJob::dispatch($importacao->id);

        $this->reset(['chaveNfe', 'payloadXml']);

        $this->dispatch('inventory-updated');
    }

    public function render()
    {
        return view('livewire.xml-import-form', [
            'importacoes' => XmlImportacao::query()->latest()->limit(5)->get(),
        ]);
    }
}
