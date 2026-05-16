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
        <select wire:model.live="incidentStatusFilter" class="rounded-lg border-slate-300 text-sm">
            <option value="">Todos os incidentes</option>
            <option value="open">Open</option>
            <option value="acknowledged">Acknowledged</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
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

            @error('scenarioName') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('baselineFlowName') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('throughputPerMinute') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('p95LatencyMs') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('errorRate') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('environmentNotes') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror

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

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Incidentes operacionais</h3>
            <div class="mt-4 space-y-4">
                @forelse ($recentIncidents as $incident)
                    <div class="rounded-lg border border-slate-100 p-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <div class="font-medium text-slate-900">{{ $incident->incident_key }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $incident->summary }}</div>
                                <div class="mt-2 text-xs text-slate-500">
                                    Fluxo: {{ $incident->flow_name }} | Severidade: {{ $incident->severity->value }} | Status: {{ $incident->status->value }}
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if ($incident->status->value === 'open')
                                    <button wire:click="acknowledgeIncident({{ $incident->id }})" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700">
                                        Reconhecer
                                    </button>
                                @endif
                                @if (in_array($incident->status->value, ['open', 'acknowledged'], true))
                                    <button wire:click="resolveIncident({{ $incident->id }})" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700">
                                        Marcar resolvido
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if ($incident->evidences->isNotEmpty())
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="text-left text-slate-500">
                                        <tr>
                                            <th class="pb-2 pr-4">Execucao</th>
                                            <th class="pb-2 pr-4">Resultado</th>
                                            <th class="pb-2 pr-4">Operador</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-slate-700">
                                        @foreach ($incident->evidences as $evidence)
                                            <tr class="border-t border-slate-100">
                                                <td class="py-2 pr-4">{{ $evidence->execution_type }}</td>
                                                <td class="py-2 pr-4">{{ $evidence->result_status->value }}</td>
                                                <td class="py-2 pr-4">{{ $evidence->operator?->name ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-500">
                        Nenhum incidente operacional para os filtros atuais.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Acoes de runbook e encerramento</h3>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <select wire:model.defer="selectedIncidentId" class="rounded-lg border-slate-300 text-sm md:col-span-2">
                    <option value="">Selecione o incidente</option>
                    @foreach ($recentIncidents as $incident)
                        <option value="{{ $incident->id }}">{{ $incident->incident_key }} - {{ $incident->status->value }}</option>
                    @endforeach
                </select>
                <select wire:model.defer="incidentExecutionType" class="rounded-lg border-slate-300 text-sm">
                    <option value="replay">Replay</option>
                    <option value="rollback">Rollback</option>
                    <option value="restore_validation">Restore validation</option>
                    <option value="contingency">Contingency</option>
                </select>
                <select wire:model.defer="incidentResultStatus" class="rounded-lg border-slate-300 text-sm">
                    <option value="success">Success</option>
                    <option value="partial">Partial</option>
                    <option value="failed">Failed</option>
                </select>
                <input wire:model.defer="incidentValidationChecks" type="text" class="rounded-lg border-slate-300 text-sm md:col-span-2" placeholder="Checks validados separados por virgula">
                <textarea wire:model.defer="incidentNotes" class="rounded-lg border-slate-300 text-sm md:col-span-2" rows="4" placeholder="Notas operacionais e evidencia objetiva"></textarea>
            </div>

            <div class="mt-4 flex flex-col gap-3 md:flex-row">
                <button wire:click="recordRunbookEvidence" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                    Registrar evidencia
                </button>
                <button wire:click="closeIncident" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">
                    Encerrar apos validacao
                </button>
            </div>

            @error('selectedIncidentId') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('incidentExecutionType') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('incidentResultStatus') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
            @error('incidentNotes') <div class="mt-3 text-sm text-rose-700">{{ $message }}</div> @enderror
        </div>
    </div>
</div>
