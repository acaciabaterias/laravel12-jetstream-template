<div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Billing Control Plane</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Saude comercial da base</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Monitore adimplencia, grace period, bloqueios e reativacoes a partir do catalogo central.
            </p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">MRR</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">R$ {{ number_format($summary['mrr'], 2, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-emerald-700">Ativas</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-emerald-900">{{ $summary['active_subscriptions'] }}</p>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-amber-700">Grace</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-amber-900">{{ $summary['grace_subscriptions'] }}</p>
        </div>
        <div class="rounded-3xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-rose-700">Bloqueadas</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-rose-900">{{ $summary['blocked_subscriptions'] }}</p>
        </div>
        <div class="rounded-3xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-sky-700">Reativadas 30d</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-sky-900">{{ $summary['reactivated_recently'] }}</p>
            <p class="mt-2 text-xs text-sky-700">Carteira em atraso: R$ {{ number_format($summary['overdue_exposure'], 2, ',', '.') }}</p>
        </div>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Carteira de assinantes</h2>
                <p class="mt-1 text-sm text-slate-500">Filtre por status, plano, risco operacional e texto livre.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar assinante"
                    class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400"
                >

                <select wire:model.live="statusFilter" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    <option value="all">Todos os status</option>
                    <option value="active">Ativa</option>
                    <option value="grace_period">Grace period</option>
                    <option value="blocked">Bloqueada</option>
                    <option value="cancelled">Cancelada</option>
                </select>

                <select wire:model.live="planFilter" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    <option value="all">Todos os planos</option>
                    @foreach ($availablePlans as $availablePlan)
                        <option value="{{ $availablePlan->slug }}">{{ $availablePlan->nome }}</option>
                    @endforeach
                </select>

                <select wire:model.live="riskFilter" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    <option value="all">Todos os riscos</option>
                    <option value="overdue">Carteira em atraso</option>
                    <option value="grace">Grace period</option>
                    <option value="blocked">Bloqueada</option>
                    <option value="reactivated">Reativadas</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Assinante</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Plano</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Ciclo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Risco</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($subscriptions as $subscription)
                        <tr wire:key="subscription-{{ $subscription->id }}" class="transition hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $subscription->cliente->razao_social }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $subscription->cliente->subdominio }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-700">{{ $subscription->plano->nome }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $subscription->status === 'blocked' ? 'bg-rose-100 text-rose-800' : ($subscription->status === 'grace_period' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ strtoupper($subscription->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <div>Inicio: {{ optional($subscription->data_inicio)->format('d/m/Y') }}</div>
                                <div class="mt-1 text-xs text-slate-400">Proximo ciclo: {{ optional($subscription->data_proximo_ciclo)->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                @if ($subscription->status === 'blocked')
                                    Bloqueio ativo
                                @elseif ($subscription->status === 'grace_period')
                                    Grace ate {{ optional($subscription->grace_ends_at)->format('d/m/Y') ?: 'n/d' }}
                                @elseif ($subscription->reactivated_at)
                                    Reativada em {{ $subscription->reactivated_at->format('d/m/Y H:i') }}
                                @else
                                    Operacao estavel
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                                Nenhuma assinatura encontrada para os filtros atuais.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-6 py-4">
            {{ $subscriptions->links() }}
        </div>
    </section>
</div>
