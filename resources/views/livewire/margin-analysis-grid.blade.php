<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900">Margem de lucro real</h3>
        <p class="mt-1 text-sm text-slate-500">Calcule margem líquida por bateria considerando custos e despesas operacionais.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <form wire:submit="calculate" class="space-y-4 rounded-2xl border border-slate-200 p-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Bateria</label>
                <select wire:model.live="bateriaId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecione...</option>
                    @foreach($baterias as $bateria)
                        <option value="{{ $bateria->id }}">{{ $bateria->sku }} · {{ $bateria->marca }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div><input type="number" step="0.01" wire:model.live="custoAquisicao" class="block w-full rounded-2xl border-slate-200 shadow-sm" placeholder="Custo aquisição"></div>
                <div><input type="number" step="0.01" wire:model.live="frete" class="block w-full rounded-2xl border-slate-200 shadow-sm" placeholder="Frete"></div>
                <div><input type="number" step="0.01" wire:model.live="imposto" class="block w-full rounded-2xl border-slate-200 shadow-sm" placeholder="Imposto"></div>
                <div><input type="number" step="0.01" wire:model.live="comissao" class="block w-full rounded-2xl border-slate-200 shadow-sm" placeholder="Comissão"></div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">
                    Calcular
                </button>
            </div>
        </form>

        <div class="space-y-3">
            @forelse($margens as $margem)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="font-semibold text-slate-900">{{ $margem->bateria?->sku }}</p>
                    <p class="mt-1 text-sm text-slate-600">Margem: {{ number_format((float) $margem->margem_calculada * 100, 2, ',', '.') }}%</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">Nenhuma margem calculada ainda.</p>
            @endforelse
        </div>
    </div>
</div>
