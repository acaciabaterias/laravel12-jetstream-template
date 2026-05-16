<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900">Laudo e cobrança</h3>
        <p class="mt-1 text-sm text-slate-500">Atualize o laudo técnico, marque procedência e dispare a comunicação ao cliente.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="space-y-3">
            @foreach($garantias as $garantia)
                <button type="button" wire:click="loadGuarantee({{ $garantia->id }})" class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left transition hover:bg-slate-100">
                    <p class="font-medium text-slate-900">OS #{{ $garantia->id }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ ucfirst($garantia->status) }}</p>
                </button>
            @endforeach
        </div>

        <form wire:submit="save" class="space-y-4 rounded-2xl border border-slate-200 p-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Laudo</label>
                <textarea wire:model.live="laudo" rows="4" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Resultado</label>
                    <select wire:model.live="resultado" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="procedente">Procedente</option>
                        <option value="improcedente">Improcedente</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Cobrança</label>
                    <input type="number" step="0.01" wire:model.live="cobrancaValor" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Salvar laudo
                </button>
            </div>
        </form>
    </div>
</div>
