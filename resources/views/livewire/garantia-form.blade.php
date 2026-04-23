<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900">Abertura de garantia</h3>
        <p class="mt-1 text-sm text-slate-500">Abra OS de garantia, relacione vale original e libere bateria de empréstimo.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
        <form wire:submit="openGuarantee" class="space-y-4 rounded-2xl border border-slate-200 p-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Cliente</label>
                <select wire:model.live="clienteId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecione...</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->razao_social }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Bateria em analise</label>
                <select wire:model.live="bateriaId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecione...</option>
                    @foreach($baterias as $bateria)
                        <option value="{{ $bateria->id }}">{{ $bateria->sku }} · {{ $bateria->marca }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Vale original</label>
                <select wire:model.live="valeOriginalId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">OS avulsa</option>
                    @foreach($vales as $vale)
                        <option value="{{ $vale->id }}">Vale #{{ $vale->id }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                    Abrir OS garantia
                </button>
            </div>
        </form>

        <form wire:submit="checkoutLoanBattery" class="space-y-4 rounded-2xl border border-slate-200 p-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Bateria de empréstimo</label>
                <select wire:model.live="bateriaEmprestimoId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecione...</option>
                    @foreach($baterias as $bateria)
                        <option value="{{ $bateria->id }}">{{ $bateria->sku }} · {{ $bateria->marca }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Devolução prevista</label>
                <input type="datetime-local" wire:model.live="dataDevolucaoPrevista" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">
                    Liberar empréstimo
                </button>
            </div>
        </form>
    </div>
</div>
