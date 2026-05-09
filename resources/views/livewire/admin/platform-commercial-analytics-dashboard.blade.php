<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Analytics comercial da plataforma</h2>
                <p class="mt-1 text-sm text-slate-600">Consolide MRR, churn, inadimplencia, recuperacao e risco comercial com recortes reutilizaveis.</p>
            </div>
            <div class="flex items-center gap-3">
                <input wire:model.live="periodDays" type="number" min="7" step="1" class="w-24 rounded-lg border-slate-300 text-sm">
                <button wire:click="rebuild" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                    Reconstruir snapshot
                </button>
            </div>
        </div>
    </div>

    @if (session()->has('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-4 xl:grid-cols-8">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">MRR</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">R$ {{ number_format((float) $summary['mrr_amount'], 2, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Churn</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['churn_count'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Taxa churn</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format((float) $summary['churn_rate'] * 100, 2, ',', '.') }}%</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Inadimplentes</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['delinquent_count'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Recuperados</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['recovered_count'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Valor recuperado</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">R$ {{ number_format((float) $summary['recovered_amount'], 2, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Bloqueados</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['blocked_count'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Referencia</div>
            <div class="mt-2 text-sm font-semibold text-slate-900">{{ $summary['reference_date'] }}</div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-base font-semibold text-slate-900">Coortes comerciais</h3>
                <input wire:model.live.debounce.300ms="cohortSearch" type="text" placeholder="Buscar coorte" class="rounded-lg border-slate-300 text-sm">
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-slate-500">
                        <tr>
                            <th class="pb-2 pr-4">Coorte</th>
                            <th class="pb-2 pr-4">Ativas</th>
                            <th class="pb-2 pr-4">Canceladas</th>
                            <th class="pb-2 pr-4">MRR</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700">
                        @foreach ($cohorts as $cohort)
                            <tr class="border-t border-slate-100">
                                <td class="py-2 pr-4">{{ $cohort->cohort_label }}</td>
                                <td class="py-2 pr-4">{{ $cohort->active_subscriptions }}</td>
                                <td class="py-2 pr-4">{{ $cohort->cancelled_subscriptions }}</td>
                                <td class="py-2 pr-4">R$ {{ number_format((float) $cohort->mrr_amount, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $cohorts->links() }}</div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-base font-semibold text-slate-900">Performance por canal</h3>
                <select wire:model.live="channelType" class="rounded-lg border-slate-300 text-sm">
                    <option value="all">Todos</option>
                    <option value="billing">Billing</option>
                    <option value="recovery">Recovery</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-slate-500">
                        <tr>
                            <th class="pb-2 pr-4">Tipo</th>
                            <th class="pb-2 pr-4">Canal</th>
                            <th class="pb-2 pr-4">Total</th>
                            <th class="pb-2 pr-4">Conversao</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700">
                        @foreach ($channels as $channel)
                            <tr class="border-t border-slate-100">
                                <td class="py-2 pr-4">{{ $channel->channel_type->value }}</td>
                                <td class="py-2 pr-4">{{ $channel->channel_name }}</td>
                                <td class="py-2 pr-4">{{ $channel->total_cases }}</td>
                                <td class="py-2 pr-4">{{ number_format((float) $channel->conversion_rate * 100, 2, ',', '.') }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $channels->links() }}</div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="text-base font-semibold text-slate-900">Insights de risco</h3>
            <select wire:model.live="riskType" class="rounded-lg border-slate-300 text-sm">
                <option value="all">Todos os riscos</option>
                <option value="delinquency">Inadimplencia</option>
                <option value="recovery_stall">Recovery stall</option>
                <option value="payment_failure">Falha de pagamento</option>
                <option value="churn">Churn</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Risco</th>
                        <th class="pb-2 pr-4">Severidade</th>
                        <th class="pb-2 pr-4">Contas</th>
                        <th class="pb-2 pr-4">Exposicao</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @foreach ($risks as $risk)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $risk->risk_type->value }}</td>
                            <td class="py-2 pr-4">{{ $risk->severity }}</td>
                            <td class="py-2 pr-4">{{ $risk->total_accounts }}</td>
                            <td class="py-2 pr-4">R$ {{ number_format((float) $risk->total_exposure, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $risks->links() }}</div>
    </div>
</div>
