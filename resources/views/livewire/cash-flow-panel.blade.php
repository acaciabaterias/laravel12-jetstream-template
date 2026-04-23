<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Fluxo de caixa projetado</h3>
            <p class="mt-1 text-sm text-slate-500">Projete saldo futuro com base em recebíveis e cobranças operacionais.</p>
        </div>
        <button type="button" wire:click="refreshProjection" class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
            Atualizar
        </button>
    </div>

    <div class="space-y-3">
        @forelse($projecoes as $projecao)
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="font-semibold text-slate-900">{{ $projecao->data_referencia->format('d/m/Y') }}</p>
                <p class="mt-1 text-sm text-slate-600">Receber: R$ {{ number_format((float) $projecao->total_receber, 2, ',', '.') }}</p>
                <p class="mt-1 text-sm text-slate-600">Pagar: R$ {{ number_format((float) $projecao->total_pagar, 2, ',', '.') }}</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">Saldo: R$ {{ number_format((float) $projecao->saldo_projetado, 2, ',', '.') }}</p>
            </div>
        @empty
            <p class="text-sm text-slate-500">Nenhuma projeção registrada.</p>
        @endforelse
    </div>
</div>
