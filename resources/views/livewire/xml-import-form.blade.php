<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900">Importacao XML</h3>
        <p class="mt-1 text-sm text-slate-500">Registre XMLs processados e bloqueie chaves duplicadas no tenant atual.</p>
    </div>

    <form wire:submit="importar" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Chave NFe</label>
                <input type="text" wire:model.live="chaveNfe" maxlength="44" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('chaveNfe') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Status</label>
                <select wire:model.live="status" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="processado">Processado</option>
                    <option value="pendente">Pendente</option>
                    <option value="erro">Erro</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Payload XML</label>
                <textarea wire:model.live="payloadXml" rows="4" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="<nfe>...</nfe>"></textarea>
                @error('payloadXml') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Log de erros</label>
                <textarea wire:model.live="logErros" rows="2" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">
                Registrar importacao
            </button>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Chave</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($importacoes as $importacao)
                    <tr>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $importacao->chave_nfe }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ ucfirst($importacao->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-6 text-center text-sm text-slate-500">Nenhuma importacao registrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
