<div class="space-y-6">
    <div class="overflow-hidden rounded-3xl border border-amber-200 bg-linear-to-br from-amber-50 via-white to-slate-50 shadow-sm">
        <div class="flex flex-col gap-5 p-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-amber-700">Executive reporting hub</p>
                <h2 class="text-2xl font-semibold text-slate-900">Dashboard executivo expandido com exportacao governada</h2>
                <p class="text-sm text-slate-600">Cruze receita, carteira, recovery e falhas de pagamento no mesmo recorte e gere relatorios Excel ou PDF com historico reexecutavel.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button wire:click="captureSnapshot" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white">Atualizar snapshot</button>
                <button wire:click="exportExcel" class="rounded-full border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-800">Exportar Excel</button>
                <button wire:click="exportPdf" class="rounded-full border border-rose-300 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-800">Exportar PDF</button>
            </div>
        </div>
    </div>

    @if ($operationMessage)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ $operationMessage }}
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-6">
        <label class="space-y-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Periodo</span>
            <input wire:model.live="periodDays" type="number" min="7" max="365" class="w-full rounded-xl border-slate-300 text-sm">
        </label>
        <label class="space-y-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Plano</span>
            <select wire:model.live="planFilter" class="w-full rounded-xl border-slate-300 text-sm">
                <option value="all">Todos</option>
                @foreach ($availablePlans as $plan)
                    <option value="{{ $plan->slug }}">{{ $plan->nome }}</option>
                @endforeach
            </select>
        </label>
        <label class="space-y-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Canal</span>
            <select wire:model.live="channelFilter" class="w-full rounded-xl border-slate-300 text-sm">
                <option value="all">Todos</option>
                <option value="pix">Pix</option>
                <option value="boleto">Boleto</option>
                <option value="manual">Manual</option>
            </select>
        </label>
        <label class="space-y-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Carteira</span>
            <select wire:model.live="portfolioFilter" class="w-full rounded-xl border-slate-300 text-sm">
                <option value="all">Todas</option>
                <option value="active">Ativa</option>
                <option value="grace_period">Grace period</option>
                <option value="blocked">Bloqueada</option>
                <option value="cancelled">Cancelada</option>
            </select>
        </label>
        <label class="space-y-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recovery</span>
            <select wire:model.live="recoveryStatusFilter" class="w-full rounded-xl border-slate-300 text-sm">
                <option value="all">Todos</option>
                <option value="open">Open</option>
                <option value="paused">Paused</option>
                <option value="escalated">Escalated</option>
                <option value="recovered">Recovered</option>
                <option value="closed">Closed</option>
            </select>
        </label>
        <div class="grid gap-3">
            <label class="space-y-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Formato</span>
                <select wire:model.live="exportFormatFilter" class="w-full rounded-xl border-slate-300 text-sm">
                    <option value="all">Todos</option>
                    <option value="excel">Excel</option>
                    <option value="pdf">PDF</option>
                </select>
            </label>
            <label class="space-y-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status export</span>
                <select wire:model.live="exportStatusFilter" class="w-full rounded-xl border-slate-300 text-sm">
                    <option value="all">Todos</option>
                    <option value="completed">Completed</option>
                    <option value="reexecuted">Reexecuted</option>
                    <option value="failed">Failed</option>
                </select>
            </label>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">MRR</div>
            <div class="mt-3 text-2xl font-semibold text-slate-900">R$ {{ number_format((float) ($snapshot['kpis']['mrr'] ?? 0), 2, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ativas</div>
            <div class="mt-3 text-2xl font-semibold text-slate-900">{{ $snapshot['kpis']['active_subscriptions'] ?? 0 }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bloqueadas</div>
            <div class="mt-3 text-2xl font-semibold text-slate-900">{{ $snapshot['kpis']['blocked_subscriptions'] ?? 0 }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Exposicao vencida</div>
            <div class="mt-3 text-2xl font-semibold text-slate-900">R$ {{ number_format((float) ($snapshot['kpis']['overdue_exposure'] ?? 0), 2, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Contas em risco</div>
            <div class="mt-3 text-2xl font-semibold text-slate-900">{{ $snapshot['kpis']['at_risk_accounts'] ?? 0 }}</div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Planos no recorte</h3>
                    <p class="text-sm text-slate-500">Compare assinaturas e MRR por plano.</p>
                </div>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">{{ count($snapshot['drilldowns']['plans'] ?? []) }} grupos</span>
            </div>
            <div class="mt-4 space-y-3">
                @foreach (($snapshot['drilldowns']['plans'] ?? []) as $plan)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm font-medium text-slate-900">{{ $plan['label'] }}</div>
                            <div class="text-sm text-slate-500">{{ $plan['subscriptions'] }} assinaturas</div>
                        </div>
                        <div class="mt-2 text-sm text-slate-600">MRR R$ {{ number_format((float) $plan['mrr'], 2, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Canais e cobranca</h3>
                    <p class="text-sm text-slate-500">Volume financeiro por canal do recorte.</p>
                </div>
                <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-800">{{ count($snapshot['drilldowns']['channels'] ?? []) }} canais</span>
            </div>
            <div class="mt-4 space-y-3">
                @foreach (($snapshot['drilldowns']['channels'] ?? []) as $channel)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm font-medium text-slate-900">{{ strtoupper($channel['label']) }}</div>
                            <div class="text-sm text-slate-500">{{ $channel['invoices'] }} cobrancas</div>
                        </div>
                        <div class="mt-2 text-sm text-slate-600">Valor R$ {{ number_format((float) $channel['amount'], 2, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Carteira</h3>
            <div class="mt-4 grid gap-3">
                @foreach (($snapshot['drilldowns']['portfolios'] ?? []) as $portfolio)
                    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-700">
                        <span>{{ $portfolio['label'] }}</span>
                        <span class="font-medium text-slate-900">{{ $portfolio['subscriptions'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Recovery</h3>
            <div class="mt-4 grid gap-3">
                @foreach (($snapshot['drilldowns']['recovery_statuses'] ?? []) as $recoveryStatus)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium text-slate-900">{{ $recoveryStatus['label'] }}</span>
                            <span class="text-slate-500">{{ $recoveryStatus['cases'] }} casos</span>
                        </div>
                        <div class="mt-2 text-sm text-slate-600">Exposicao R$ {{ number_format((float) $recoveryStatus['exposure'], 2, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.25fr,0.75fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Historico de exportacoes</h3>
                    <p class="text-sm text-slate-500">Mesmo recorte, multiplos formatos e reexecucao governada.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $summary['recent_export_count'] ?? 0 }} registros</span>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-slate-500">
                        <tr>
                            <th class="pb-3 pr-4">ID</th>
                            <th class="pb-3 pr-4">Formato</th>
                            <th class="pb-3 pr-4">Status</th>
                            <th class="pb-3 pr-4">Escopo</th>
                            <th class="pb-3 pr-4">Acao</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700">
                        @foreach ($exports as $export)
                            <tr class="border-t border-slate-100 align-top">
                                <td class="py-3 pr-4 font-medium text-slate-900">#{{ $export['id'] }}</td>
                                <td class="py-3 pr-4 uppercase">{{ $export['format'] }}</td>
                                <td class="py-3 pr-4">{{ $export['status'] }}</td>
                                <td class="py-3 pr-4">{{ $export['scope_summary'] }}</td>
                                <td class="py-3 pr-4">
                                    <button wire:click="reexecuteExport({{ $export['id'] }})" class="rounded-full border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700">
                                        Reexecutar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Trilha de execucao</h3>
            <div class="mt-4 space-y-3">
                @foreach ($executionLogs as $log)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm font-medium text-slate-900">{{ $log['event_type'] }}</div>
                            <div class="text-xs text-slate-500">#{{ $log['export_id'] }}</div>
                        </div>
                        <div class="mt-2 text-sm text-slate-600">{{ $log['operator_name'] ?: 'Sistema' }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $log['logged_at'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
