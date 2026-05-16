<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Contingência fiscal e bancária</h3>
            <p class="mt-1 text-sm text-slate-500">Acompanhe retries, falhas externas e integrações que exigem intervenção.</p>
        </div>
        <div class="rounded-2xl bg-rose-100 px-4 py-3">
            <p class="text-xs uppercase tracking-[0.16em] text-rose-700">Críticas</p>
            <p class="mt-1 text-xl font-semibold text-rose-950">{{ $criticas }}</p>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($contingencias as $contingencia)
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-900">{{ strtoupper($contingencia->tipo_integracao) }}</p>
                    <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ ucfirst($contingencia->status) }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-600">Tentativas: {{ $contingencia->tentativas }}</p>
                @if($contingencia->ultimo_erro)
                    <p class="mt-1 text-sm text-rose-600">{{ $contingencia->ultimo_erro }}</p>
                @endif
            </div>
        @empty
            <p class="text-sm text-slate-500">Nenhuma contingência registrada.</p>
        @endforelse
    </div>
</div>
