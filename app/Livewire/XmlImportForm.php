<?php

namespace App\Livewire;

use App\Models\XmlImportacao;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class XmlImportForm extends Component
{
    public string $chaveNfe = '';

    public string $payloadXml = '';

    public string $status = 'processado';

    public ?string $logErros = null;

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
            'status' => ['required', 'in:processado,pendente,erro'],
            'logErros' => ['nullable', 'string', 'max:2000'],
        ], [
            'chaveNfe.unique' => 'Esta chave NFe ja foi importada.',
        ]);

        XmlImportacao::query()->create([
            'chave_nfe' => $validated['chaveNfe'],
            'status' => $validated['status'],
            'log_erros' => $validated['logErros'],
            'payload_xml' => [
                'raw' => $validated['payloadXml'],
            ],
        ]);

        $this->reset(['chaveNfe', 'payloadXml', 'logErros']);
        $this->status = 'processado';

        $this->dispatch('inventory-updated');
    }

    public function render()
    {
        return view('livewire.xml-import-form', [
            'importacoes' => XmlImportacao::query()->latest()->limit(5)->get(),
        ]);
    }
}
