<div class="rounded-lg border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/60 lg:p-6">
    <div class="mb-6 flex flex-col gap-4 border-b border-slate-200 pb-5 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <div class="inline-flex rounded-lg border border-[#123b66]/15 bg-[#123b66]/5 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#123b66]">
                Radar de estoque
            </div>
            <h3 class="mt-4 text-2xl font-semibold tracking-tight text-slate-950">Dashboard de estoque</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Monitore saldo, itens em alerta, valor estocado e o ritmo de saídas da operação.</p>
        </div>
        <div class="w-full xl:max-w-sm">
            <label class="block text-sm font-semibold text-slate-700">Buscar bateria</label>
            <input type="text" wire:model.live.debounce.300ms="filtroBusca" class="mt-2 block w-full rounded-lg border-slate-200 bg-slate-50 shadow-sm focus:border-[#123b66] focus:ring-[#123b66]" placeholder="SKU ou marca">
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg bg-[#123b66] px-5 py-5 text-white shadow-lg shadow-[#123b66]/20">
            <p class="text-xs uppercase tracking-[0.18em] text-white/60">Produtos em alerta</p>
            <p class="mt-3 text-3xl font-semibold">{{ $produtosEmAlerta }}</p>
            <p class="mt-2 text-sm text-white/65">Itens com saldo igual ou abaixo de 5.</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-slate-50 px-5 py-5">
            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Total em estoque</p>
            <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $saldoTotal }}</p>
            <p class="mt-2 text-sm text-slate-500">Soma dos saldos disponíveis.</p>
        </div>
        <div class="rounded-lg bg-[#f59e0b] px-5 py-5 text-slate-950 shadow-lg shadow-[#f59e0b]/20">
            <p class="text-xs uppercase tracking-[0.18em] text-slate-700">Valor total</p>
            <p class="mt-3 text-3xl font-semibold">R$ {{ number_format($valorTotalEstoque, 2, ',', '.') }}</p>
            <p class="mt-2 text-sm text-slate-800">Saldo multiplicado pelo preço de venda.</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.02fr_0.98fr]">
        <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-5">
            <h4 class="text-lg font-semibold text-slate-950">Saídas por período</h4>
            <p class="mt-1 text-sm text-slate-500">Últimos 7 dias de movimentação de saída confirmada.</p>
            <div class="mt-5 h-72 rounded-lg bg-white p-4">
                <canvas id="inventory-output-chart"></canvas>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-5">
            <h4 class="text-lg font-semibold text-slate-950">Produtos mais vendidos</h4>
            <p class="mt-1 text-sm text-slate-500">Top baterias com base nos itens lançados em vales.</p>
            <div class="mt-5 overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Produto</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Qtd</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($maisVendidos as $item)
                            <tr>
                                <td class="px-4 py-4 text-sm text-slate-700">{{ $item->bateria?->sku }} · {{ $item->bateria?->marca }}</td>
                                <td class="px-4 py-4 text-right text-sm font-semibold text-slate-950">{{ (int) $item->total_vendido }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-sm text-slate-500">Sem histórico suficiente de vendas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-lg border border-slate-200 bg-white p-5">
        <h4 class="text-lg font-semibold text-slate-950">Alertas de shelf life</h4>
        <p class="mt-1 text-sm text-slate-500">Itens acima de {{ $limiteShelfLife }} dias desde a última entrada.</p>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Produto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Depósito</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Dias</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($alertasShelfLife as $alerta)
                        <tr>
                            <td class="px-4 py-4 text-sm text-slate-700">{{ $alerta['sku'] }} · {{ $alerta['marca'] }}</td>
                            <td class="px-4 py-4 text-sm text-slate-600">{{ $alerta['deposito'] }}</td>
                            <td class="px-4 py-4 text-right text-sm font-semibold text-amber-700">{{ $alerta['dias_em_estoque'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">Nenhum item acima do limite de shelf life.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Bateria</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Depósito</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Saldo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($saldos as $saldo)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-4 text-sm text-slate-700">{{ $saldo->bateria?->sku }} · {{ $saldo->bateria?->marca }}</td>
                        <td class="px-4 py-4 text-sm text-slate-600">{{ $saldo->deposito?->nome }}</td>
                        <td class="px-4 py-4 text-right text-sm font-semibold {{ $saldo->quantidade_atual <= 5 ? 'text-amber-600' : 'text-slate-900' }}">{{ $saldo->quantidade_atual }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-sm text-slate-500">Nenhum saldo registrado até o momento.</td>
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
                const chartElement = document.getElementById('inventory-output-chart');

                if (!chartElement || !window.Chart) {
                    return;
                }

                if (window.inventoryOutputChart) {
                    window.inventoryOutputChart.destroy();
                }

                window.inventoryOutputChart = new window.Chart(chartElement, {
                    type: 'bar',
                    data: {
                        labels: @json($saidasPorPeriodo['labels']),
                        datasets: [
                            {
                                label: 'Saídas',
                                data: @json($saidasPorPeriodo['valores']),
                                backgroundColor: '#123b66',
                                borderRadius: 12,
                            }
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
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
