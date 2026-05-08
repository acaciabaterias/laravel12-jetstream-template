<div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Platform Payments</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Saude de cobrancas e conciliacao</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Monitore cobrancas emitidas, liquidadas e excecoes de reconciliação no plano central.
            </p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Pendentes</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $summary['pending_charges'] }}</p>
        </div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-emerald-700">Liquidadas</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-emerald-900">{{ $summary['paid_charges'] }}</p>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-amber-700">Excecoes abertas</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-amber-900">{{ $summary['open_exceptions'] }}</p>
        </div>
        <div class="rounded-3xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-rose-700">Chargebacks</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-rose-900">{{ $summary['chargeback_cases'] }}</p>
        </div>
        <div class="rounded-3xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-sky-700">Exposicao pendente</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-sky-900">R$ {{ number_format($summary['pending_exposure'], 2, ',', '.') }}</p>
        </div>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Carteira de cobrancas</h2>
                <p class="mt-1 text-sm text-slate-500">Filtre por status, canal, excecao e busca textual.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar cobranca"
                    class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400"
                >

                <select wire:model.live="statusFilter" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    <option value="all">Todos os status</option>
                    <option value="submitted">Emitida</option>
                    <option value="pending">Pendente</option>
                    <option value="paid">Liquidada</option>
                    <option value="failed">Falhou</option>
                    <option value="chargeback">Chargeback</option>
                </select>

                <select wire:model.live="channelFilter" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    <option value="all">Todos os canais</option>
                    <option value="boleto">Boleto</option>
                    <option value="pix">PIX</option>
                </select>

                <select wire:model.live="exceptionFilter" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    <option value="all">Todas as excecoes</option>
                    <option value="amount_mismatch">Valor divergente</option>
                    <option value="reference_mismatch">Referencia divergente</option>
                    <option value="chargeback">Chargeback</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Assinante</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Referencia</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Canal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Excecoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($charges as $charge)
                        <tr wire:key="charge-{{ $charge->id }}" class="transition hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $charge->fatura->cliente->razao_social }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $charge->gateway->nome }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ $charge->external_reference }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ strtoupper($charge->payment_channel) }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ strtoupper($charge->status->value) }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                @php($exceptions = $charge->fatura->excecoesConciliacao)
                                @if ($exceptions->isEmpty())
                                    Operacao estavel
                                @else
                                    {{ $exceptions->pluck('exception_type')->join(', ') }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                                Nenhuma cobranca encontrada para os filtros atuais.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-6 py-4">
            {{ $charges->links() }}
        </div>
    </section>
</div>
