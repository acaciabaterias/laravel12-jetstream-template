<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Observabilidade operacional</h2>
                <p class="mt-1 text-sm text-slate-600">Monitore backlog, latencia, falha e sinais de degradacao dos fluxos centrais.</p>
            </div>
            <button wire:click="rebuild" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                Reavaliar saude operacional
            </button>
        </div>
    </div>

    @if ($operationMessage)
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ $operationMessage }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">Saudaveis</div><div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['healthy'] }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">Warnings</div><div class="mt-2 text-2xl font-semibold text-amber-700">{{ $summary['warning'] }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">Criticos</div><div class="mt-2 text-2xl font-semibold text-rose-700">{{ $summary['critical'] }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">Coletores indisponiveis</div><div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['unavailable_collectors'] }}</div></div>
    </div>

    <div class="grid gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-3">
        <select wire:model.live="flowNameFilter" class="rounded-lg border-slate-300 text-sm">
            <option value="">Todos os fluxos</option>
            <option value="integration_backbone">Integration backbone</option>
            <option value="platform_payments">Platform payments</option>
            <option value="platform_recovery">Platform recovery</option>
            <option value="platform_analytics">Platform analytics</option>
        </select>
        <select wire:model.live="severityFilter" class="rounded-lg border-slate-300 text-sm">
            <option value="">Todas as severidades</option>
            <option value="healthy">Healthy</option>
            <option value="warning">Warning</option>
            <option value="critical">Critical</option>
        </select>
        <select wire:model.live="statusFilter" class="rounded-lg border-slate-300 text-sm">
            <option value="">Todos os status</option>
            <option value="healthy">Healthy</option>
            <option value="degraded">Degraded</option>
            <option value="unavailable">Unavailable</option>
        </select>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Ultima leitura por fluxo</h3>
            <div class="mt-4 space-y-3">
                @foreach ($latestSnapshots as $snapshot)
                    <div class="rounded-lg border border-slate-100 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-medium text-slate-900">{{ $snapshot->flow_name }}</div>
                            <div class="text-sm text-slate-500">{{ $snapshot->severity->value }}</div>
                        </div>
                        <div class="mt-2 text-sm text-slate-600">
                            Backlog: {{ $snapshot->backlog_count }} | Falha: {{ number_format((float) $snapshot->failure_rate * 100, 2, ',', '.') }}% | Replays: {{ $snapshot->open_replays }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Historico operacional</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-slate-500">
                        <tr>
                            <th class="pb-2 pr-4">Fluxo</th>
                            <th class="pb-2 pr-4">Status</th>
                            <th class="pb-2 pr-4">Severidade</th>
                            <th class="pb-2 pr-4">Backlog</th>
                            <th class="pb-2 pr-4">Falha</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700">
                        @foreach ($snapshots as $snapshot)
                            <tr class="border-t border-slate-100">
                                <td class="py-2 pr-4">{{ $snapshot->flow_name }}</td>
                                <td class="py-2 pr-4">{{ $snapshot->status->value }}</td>
                                <td class="py-2 pr-4">{{ $snapshot->severity->value }}</td>
                                <td class="py-2 pr-4">{{ $snapshot->backlog_count }}</td>
                                <td class="py-2 pr-4">{{ number_format((float) $snapshot->failure_rate * 100, 2, ',', '.') }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $snapshots->links() }}</div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Registrar e comparar baseline</h3>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <input wire:model.defer="scenarioName" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="Cenario critico">
                <select wire:model.defer="baselineFlowName" class="rounded-lg border-slate-300 text-sm">
                    <option value="">Selecione o fluxo</option>
                    @foreach ($availableFlows as $flow)
                        <option value="{{ $flow }}">{{ $flow }}</option>
                    @endforeach
                </select>
                <input wire:model.defer="throughputPerMinute" type="number" min="1" class="rounded-lg border-slate-300 text-sm" placeholder="Throughput por minuto">
                <input wire:model.defer="p95LatencyMs" type="number" min="1" class="rounded-lg border-slate-300 text-sm" placeholder="Latencia p95 em ms">
                <input wire:model.defer="errorRate" type="number" min="0" max="1" step="0.0001" class="rounded-lg border-slate-300 text-sm" placeholder="Taxa de erro 0-1">
                <input wire:model.defer="environmentNotes" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="Observacoes de ambiente">
            </div>

            <div class="mt-4 flex flex-col gap-3 md:flex-row">
                <button wire:click="saveBaseline" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                    Registrar baseline
                </button>
                <button wire:click="compareBaseline" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">
                    Comparar execucao
                </button>
            </div>

            @error('scenario_name') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('baseline_flow_name') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('throughput_per_minute') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('p95_latency_ms') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('error_rate') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('environment_notes') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror

            @if ($comparisonResult)
                <div class="mt-6 rounded-lg border border-slate-100 bg-slate-50 p-4">
                    <div class="text-sm font-medium text-slate-900">
                        Resultado da comparacao: {{ $comparisonResult['status'] }}
                    </div>
                    @if (($comparisonResult['baseline'] ?? null) !== null)
                        <div class="mt-2 text-sm text-slate-600">
                            Baseline aceito em {{ $comparisonResult['baseline']['accepted_at'] }}
                        </div>
                    @endif
                    @if (($comparisonResult['checks'] ?? []) !== [])
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="text-left text-slate-500">
                                    <tr>
                                        <th class="pb-2 pr-4">Metrica</th>
                                        <th class="pb-2 pr-4">Baseline</th>
                                        <th class="pb-2 pr-4">Atual</th>
                                        <th class="pb-2 pr-4">Limite</th>
                                        <th class="pb-2 pr-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-slate-700">
                                    @foreach ($comparisonResult['checks'] as $metric => $check)
                                        <tr class="border-t border-slate-100">
                                            <td class="py-2 pr-4">{{ $metric }}</td>
                                            <td class="py-2 pr-4">{{ $check['baseline'] }}</td>
                                            <td class="py-2 pr-4">{{ $check['current'] }}</td>
                                            <td class="py-2 pr-4">{{ $check['threshold'] }}</td>
                                            <td class="py-2 pr-4">{{ $check['regressed'] ? 'regressed' : 'ok' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Baselines recentes</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-slate-500">
                        <tr>
                            <th class="pb-2 pr-4">Cenario</th>
                            <th class="pb-2 pr-4">Fluxo</th>
                            <th class="pb-2 pr-4">Throughput</th>
                            <th class="pb-2 pr-4">Latencia p95</th>
                            <th class="pb-2 pr-4">Erro</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700">
                        @forelse ($recentBaselines as $baseline)
                            <tr class="border-t border-slate-100">
                                <td class="py-2 pr-4">{{ $baseline->scenario_name }}</td>
                                <td class="py-2 pr-4">{{ $baseline->flow_name }}</td>
                                <td class="py-2 pr-4">{{ $baseline->throughput_per_minute }}</td>
                                <td class="py-2 pr-4">{{ $baseline->p95_latency_ms }} ms</td>
                                <td class="py-2 pr-4">{{ number_format((float) $baseline->error_rate * 100, 2, ',', '.') }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-slate-500">Nenhum baseline registrado para os filtros atuais.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
