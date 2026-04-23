<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900">CNAB e uploads</h3>
        <p class="mt-1 text-sm text-slate-500">Registre retornos CNAB e acompanhe o processamento externo com rastreabilidade.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <form wire:submit="registerUpload" class="space-y-4 rounded-2xl border border-slate-200 p-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Remessa vinculada</label>
                <select wire:model.live="cnabRemessaId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Sem remessa vinculada</option>
                    @foreach($remessas as $remessa)
                        <option value="{{ $remessa->id }}">{{ $remessa->nome_arquivo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Nome do arquivo</label>
                <input type="text" wire:model.live="nomeArquivo" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="retorno.ret">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                    Registrar upload
                </button>
            </div>
        </form>

        <div class="space-y-3">
            @forelse($uploads as $upload)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="font-semibold text-slate-900">{{ $upload->nome_arquivo }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ ucfirst($upload->status_processamento) }}</p>
                    @if($upload->log_processamento)
                        <p class="mt-1 text-sm text-rose-600">{{ $upload->log_processamento }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500">Nenhum upload registrado.</p>
            @endforelse
        </div>
    </div>
</div>
