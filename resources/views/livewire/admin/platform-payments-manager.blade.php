<div class="space-y-8">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Platform Payments</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Emissao de cobrancas SaaS</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Emita cobrancas externas para faturas centrais e acompanhe as ultimas tentativas registradas.</p>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1.05fr),minmax(0,0.95fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Emitir cobranca</h2>

            <form wire:submit="save" class="mt-6 grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Fatura SaaS</label>
                    <select wire:model="faturaSaaSId" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        <option value="">Selecione</option>
                        @foreach ($faturas as $fatura)
                            <option value="{{ $fatura->id }}">
                                {{ $fatura->cliente->razao_social }} · {{ $fatura->referencia }} · R$ {{ number_format((float) $fatura->valor, 2, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('faturaSaaSId') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Gateway</label>
                    <select wire:model="gatewayCobrancaSaaSId" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        <option value="">Selecione</option>
                        @foreach ($gateways as $gateway)
                            <option value="{{ $gateway->id }}">{{ $gateway->nome }}</option>
                        @endforeach
                    </select>
                    @error('gatewayCobrancaSaaSId') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Canal</label>
                    <select wire:model="paymentChannel" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        <option value="boleto">Boleto</option>
                        <option value="pix">PIX</option>
                    </select>
                </div>
                <label class="md:col-span-2 flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700">
                    <input type="checkbox" wire:model="forceReissue" class="rounded border-slate-300 text-slate-900 focus:ring-slate-400">
                    Permitir reemissao controlada
                </label>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Justificativa</label>
                    <input type="text" wire:model="reason" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition hover:bg-slate-800">
                        Emitir cobranca
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Ultimas cobrancas externas</h2>
            <div class="mt-5 space-y-4">
                @forelse ($charges as $charge)
                    <article class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $charge->fatura->cliente->razao_social }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $charge->gateway->nome }} · {{ strtoupper($charge->payment_channel) }}</p>
                            </div>
                            <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">
                                {{ strtoupper($charge->status->value) }}
                            </span>
                        </div>
                        <div class="mt-4 grid gap-3 text-sm text-slate-600">
                            <p>Referencia: {{ $charge->external_reference }}</p>
                            <p>Valor: R$ {{ number_format((float) $charge->valor_emitido, 2, ',', '.') }}</p>
                            <p>Vencimento: {{ optional($charge->vencimento_emitido)->format('d/m/Y') }}</p>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-slate-500">Nenhuma cobranca externa registrada.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
