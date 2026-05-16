<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5">
        <h3 class="text-lg font-semibold text-slate-900">Ordens de servico</h3>
        <p class="mt-1 text-sm text-slate-500">Atualize laudos e observacoes de atendimentos tecnicos em andamento.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="space-y-3">
            @forelse($ordensServico as $ordemServico)
                <button type="button" wire:click="loadOrder({{ $ordemServico->id }})" class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left transition hover:bg-slate-100">
                    <p class="font-medium text-slate-900">OS #{{ $ordemServico->id }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ ucfirst($ordemServico->status) }}</p>
                </button>
            @empty
                <p class="text-sm text-slate-500">Nenhuma ordem de servico registrada.</p>
            @endforelse
        </div>

        <form wire:submit="save" class="space-y-4 rounded-2xl border border-slate-200 p-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Laudo tecnico</label>
                <textarea wire:model.live="laudo" rows="4" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Observacoes</label>
                <textarea wire:model.live="observacoes" rows="4" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Salvar OS
                </button>
            </div>
        </form>
    </div>
</div>
