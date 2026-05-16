<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Painel logistico</h3>
            <p class="mt-1 text-sm text-slate-500">Acompanhe rotas ativas, entregadores em campo e eventos operacionais recentes.</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="space-y-3">
            @forelse($rotasAtivas as $rota)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-slate-900">Rota #{{ $rota->id }}</p>
                        <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ ucfirst($rota->status) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">Entregador: {{ $rota->entregador->name }}</p>
                    <p class="mt-1 text-sm text-slate-600">Paradas: {{ $rota->pontos->count() }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">Nenhuma rota ativa.</p>
            @endforelse
        </div>

        <div class="rounded-2xl border border-slate-200 p-4">
            <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">Eventos recentes</h4>
            <div class="mt-4 space-y-3">
                @forelse($eventosRecentes as $evento)
                    <div class="rounded-2xl bg-slate-50 p-3 text-sm text-slate-600">
                        <p class="font-medium text-slate-900">{{ ucfirst($evento->tipo_evento) }}</p>
                        <p class="mt-1">{{ optional($evento->recorded_at)->format('d/m H:i') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Nenhum evento registrado.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
