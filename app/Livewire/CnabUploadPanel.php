<?php

namespace App\Livewire;

use App\Jobs\DispatchCnabProcessingJob;
use App\Models\CnabRemessa;
use App\Models\CnabRetornoUpload;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class CnabUploadPanel extends Component
{
    public string $nomeRemessa = '';

    public string $tipoArquivo = 'retorno';

    public string $nomeArquivo = '';

    public ?int $cnabRemessaId = null;

    public function mount(): void
    {
        Gate::authorize('acesso-financeiro');
    }

    public function registerUpload(): void
    {
        Gate::authorize('acesso-financeiro');

        $validated = $this->validate([
            'nomeArquivo' => ['required', 'string', 'max:255'],
            'cnabRemessaId' => ['nullable', 'exists:cnab_remessas,id'],
        ]);

        $upload = CnabRetornoUpload::query()->create([
            'cnab_remessa_id' => $validated['cnabRemessaId'],
            'nome_arquivo' => $validated['nomeArquivo'],
            'status_processamento' => str_ends_with(strtolower($validated['nomeArquivo']), '.ret') ? 'pendente' : 'erro',
            'log_processamento' => str_ends_with(strtolower($validated['nomeArquivo']), '.ret') ? null : 'Arquivo CNAB invalido',
        ]);

        if ($upload->status_processamento === 'pendente') {
            DispatchCnabProcessingJob::dispatchSync($upload->id);
        }
    }

    public function registerRemessa(): void
    {
        Gate::authorize('acesso-financeiro');

        $validated = $this->validate([
            'nomeRemessa' => ['required', 'string', 'max:255'],
            'tipoArquivo' => ['required', 'in:remessa,retorno'],
        ]);

        CnabRemessa::query()->create([
            'tipo_arquivo' => $validated['tipoArquivo'],
            'nome_arquivo' => $validated['nomeRemessa'],
            'status' => 'gerada',
            'arquivo_path' => '/storage/cnab/remessas/'.$validated['nomeRemessa'],
        ]);

        $this->reset(['nomeRemessa']);
        $this->tipoArquivo = 'retorno';
    }

    public function render()
    {
        return view('livewire.cnab-upload-panel', [
            'remessas' => CnabRemessa::query()->latest('id')->limit(8)->get(),
            'uploads' => CnabRetornoUpload::query()->latest('id')->limit(8)->get(),
        ]);
    }
}
