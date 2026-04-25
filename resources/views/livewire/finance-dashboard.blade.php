<div class="rounded-lg border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/60 lg:p-6">
    <div class="mb-6 flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="inline-flex rounded-lg border border-[#123b66]/15 bg-[#123b66]/5 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#123b66]">
                Radar financeiro
            </div>
            <h3 class="mt-4 text-2xl font-semibold tracking-tight text-slate-950">Painel financeiro do tenant</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Acompanhe o caixa, margens e as últimas movimentações sem sair da operação principal.</p>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Pendências de conciliação:
            <span class="font-semibold">{{ $transacoesPendentes }}</span>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg bg-[#123b66] px-5 py-5 text-white shadow-lg shadow-[#123b66]/20">
            <p class="text-xs uppercase tracking-[0.18em] text-white/60">A receber</p>
            <p class="mt-3 text-3xl font-semibold">R$ {{ number_format($totalReceber, 2, ',', '.') }}</p>
            <p class="mt-2 text-sm text-white/65">Receitas registradas no tenant.</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-slate-50 px-5 py-5">
            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">A pagar</p>
            <p class="mt-3 text-3xl font-semibold text-slate-950">R$ {{ number_format($totalPagar, 2, ',', '.') }}</p>
            <p class="mt-2 text-sm text-slate-500">Despesas e compromissos lançados.</p>
        </div>
        <div class="rounded-lg bg-[#f59e0b] px-5 py-5 text-slate-950 shadow-lg shadow-[#f59e0b]/20">
            <p class="text-xs uppercase tracking-[0.18em] text-slate-700">Margem média</p>
            <p class="mt-3 text-3xl font-semibold">{{ number_format($margemMedia, 1, ',', '.') }}%</p>
            <p class="mt-2 text-sm text-slate-800">Resultado sobre receitas totais.</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
        <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h4 class="text-lg font-semibold text-slate-950">Fluxo de caixa</h4>
                    <p class="mt-1 text-sm text-slate-500">Entradas e saídas consolidadas nos últimos 7 dias.</p>
                </div>
            </div>
            <div class="mt-5 h-72 rounded-lg bg-white p-4">
                <canvas id="finance-cashflow-chart"></canvas>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-5">
                <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">Contas bancárias</h4>
                <div class="mt-4 space-y-3">
                    @forelse ($contas as $conta)
                        <div class="rounded-lg border border-slate-200 bg-white p-4">
                            <p class="font-semibold text-slate-950">{{ $conta->banco }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $conta->agencia }} / {{ $conta->conta }}</p>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-slate-200 bg-white px-4 py-8 text-sm text-slate-500">Nenhuma conta bancária cadastrada.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg border border-slate-200">
        <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
            <h4 class="text-lg font-semibold text-slate-950">Últimas transações</h4>
        </div>
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50/70">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Descrição</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Data</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Valor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($transacoesRecentes as $transacao)
                    <tr>
                        <td class="px-4 py-4 text-sm">
                            <span class="rounded-lg px-3 py-1 text-xs font-semibold {{ $transacao->tipo === 'receita' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ ucfirst($transacao->tipo) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm text-slate-600">{{ $transacao->descricao }}</td>
                        <td class="px-4 py-4 text-sm text-slate-500">{{ optional($transacao->data_transacao)->format('d/m/Y') }}</td>
                        <td class="px-4 py-4 text-right text-sm font-semibold text-slate-900">R$ {{ number_format((float) $transacao->valor, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">Sem transações recentes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (() => {
            const boot = () => {
                const chartElement = document.getElementById('finance-cashflow-chart');

                if (!chartElement || !window.Chart) {
                    return;
                }

                if (window.financeCashflowChart) {
                    window.financeCashflowChart.destroy();
                }

                window.financeCashflowChart = new window.Chart(chartElement, {
                    type: 'line',
                    data: {
                        labels: @json($fluxoCaixa['labels']),
                        datasets: [
                            {
                                label: 'Entradas',
                                data: @json($fluxoCaixa['entradas']),
                                borderColor: '#123b66',
                                backgroundColor: 'rgba(18, 59, 102, 0.12)',
                                tension: 0.35,
                                fill: true,
                            },
                            {
                                label: 'Saídas',
                                data: @json($fluxoCaixa['saidas']),
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.14)',
                                tension: 0.35,
                                fill: true,
                            }
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                            },
                        },
                    },
                });
            };

            document.addEventListener('livewire:navigated', boot, { once: true });
            window.addEventListener('load', boot, { once: true });
            boot();
        })();
    </script>
@endpush
