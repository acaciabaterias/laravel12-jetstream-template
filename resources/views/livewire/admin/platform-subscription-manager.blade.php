<div class="space-y-8">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Billing Control Plane</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Assinaturas da plataforma</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Ative novas assinaturas, vincule politicas de inadimplencia e acompanhe os contratos mais recentes.</p>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1.05fr),minmax(0,0.95fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Ativar assinatura</h2>

            <form wire:submit="save" class="mt-6 grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Assinante</label>
                    <select wire:model="clienteId" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        <option value="">Selecione</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->razao_social }}</option>
                        @endforeach
                    </select>
                    @error('cliente_id') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Plano</label>
                    <select wire:model="planoId" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        <option value="">Selecione</option>
                        @foreach ($planos as $plano)
                            <option value="{{ $plano->id }}">{{ $plano->nome }}</option>
                        @endforeach
                    </select>
                    @error('plano_id') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Politica</label>
                    <select wire:model="politicaInadimplenciaId" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        <option value="">Padrao da plataforma</option>
                        @foreach ($politicas as $politica)
                            <option value="{{ $politica->id }}">{{ $politica->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Status inicial</label>
                    <select wire:model="status" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        <option value="active">Ativa</option>
                        <option value="trial">Trial</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Inicio</label>
                    <input type="date" wire:model="dataInicio" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Proximo ciclo</label>
                    <input type="date" wire:model="dataProximoCiclo" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Justificativa</label>
                    <input type="text" wire:model="reason" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Observacoes</label>
                    <textarea wire:model="observacoes" rows="3" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400"></textarea>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition hover:bg-slate-800">
                        Ativar assinatura
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Contratos recentes</h2>
            <div class="mt-5 space-y-4">
                @forelse ($assinaturas as $assinatura)
                    <article class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $assinatura->cliente->razao_social }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $assinatura->plano->nome }} @if ($assinatura->politicaInadimplencia) · {{ $assinatura->politicaInadimplencia->nome }} @endif</p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $assinatura->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800' }}">
                                {{ strtoupper($assinatura->status) }}
                            </span>
                        </div>
                        <div class="mt-4 grid gap-3 text-sm text-slate-600">
                            <p>Inicio: {{ optional($assinatura->data_inicio)->format('d/m/Y') }}</p>
                            <p>Proximo ciclo: {{ optional($assinatura->data_proximo_ciclo)->format('d/m/Y') }}</p>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-slate-500">Nenhuma assinatura ativada ainda.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
