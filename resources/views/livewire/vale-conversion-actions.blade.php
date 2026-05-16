<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5">
        <h3 class="text-lg font-semibold text-slate-900">Conversoes e estornos</h3>
        <p class="mt-1 text-sm text-slate-500">Aponte o vale ativo e transforme em pedido, OS ou cancele com estorno da reserva.</p>
    </div>

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700">Vale alvo</label>
            <input type="number" min="1" wire:model.live="valeId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Informe o ID do vale">
        </div>

        @if($vale)
            <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                <p><span class="font-semibold text-slate-900">Status:</span> {{ ucfirst($vale->status) }}</p>
                <p class="mt-1"><span class="font-semibold text-slate-900">Reservas:</span> {{ $vale->reservas->count() }}</p>
            </div>
        @endif

        <div class="grid gap-3 md:grid-cols-3">
            <button type="button" wire:click="convertToPedido" class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                Converter em pedido
            </button>
            <button type="button" wire:click="convertToOs" class="rounded-2xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-400">
                Converter em OS
            </button>
            <button type="button" wire:click="cancelVale" class="rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-500">
                Cancelar vale
            </button>
        </div>
    </div>
</div>
