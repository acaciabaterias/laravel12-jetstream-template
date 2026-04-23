<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5">
        <h3 class="text-lg font-semibold text-slate-900">Painel de garantias</h3>
        <p class="mt-1 text-sm text-slate-500">Acompanhe o ciclo de garantia, empréstimos e notificações do pós-venda.</p>
    </div>

    <div class="space-y-3">
        @forelse($garantias as $garantia)
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-900">OS Garantia #{{ $garantia->id }}</p>
                    <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ ucfirst($garantia->status) }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-600">{{ $garantia->cliente->razao_social }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ $garantia->bateria->sku }} · {{ $garantia->bateria->marca }}</p>
            </div>
        @empty
            <p class="text-sm text-slate-500">Nenhuma garantia registrada.</p>
        @endforelse
    </div>
</div>
