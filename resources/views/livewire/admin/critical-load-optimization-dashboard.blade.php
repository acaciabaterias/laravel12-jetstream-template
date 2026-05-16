<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Critical load optimization</h2>
        <p class="mt-1 text-sm text-slate-600">Registre cenarios reproduziveis, benchmarks comparaveis e promova baselines validas para fluxos criticos.</p>
    </div>

    @if ($operationMessage)
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ $operationMessage }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">Scenarios</div><div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['scenarios'] }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">Improved</div><div class="mt-2 text-2xl font-semibold text-emerald-700">{{ $summary['improved'] }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">Stable</div><div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['stable'] }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">Regressed</div><div class="mt-2 text-2xl font-semibold text-rose-700">{{ $summary['regressed'] }}</div></div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-4">
            <select wire:model.live="flowNameFilter" class="rounded-lg border-slate-300 text-sm">
                <option value="">Todos os fluxos</option>
                <option value="integration_backbone">Integration backbone</option>
                <option value="platform_payments">Platform payments</option>
                <option value="platform_recovery">Platform recovery</option>
                <option value="platform_analytics">Platform analytics</option>
                <option value="production_observability">Production observability</option>
            </select>
            <select wire:model.live="comparisonStatusFilter" class="rounded-lg border-slate-300 text-sm">
                <option value="">Todas as comparacoes</option>
                <option value="improved">Improved</option>
                <option value="stable">Stable</option>
                <option value="regressed">Regressed</option>
            </select>
            <select wire:model.live="categoryFilter" class="rounded-lg border-slate-300 text-sm">
                <option value="">Todas as categorias</option>
                <option value="database">Database</option>
                <option value="queue">Queue</option>
                <option value="external_endpoint">External endpoint</option>
                <option value="application">Application</option>
            </select>
            <select wire:model.live="environmentFilter" class="rounded-lg border-slate-300 text-sm">
                <option value="">Todos os ambientes</option>
                <option value="staging">Staging</option>
                <option value="production">Production</option>
            </select>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Registrar cenario</h3>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <input wire:model="scenarioName" type="text" placeholder="Cenario" class="rounded-lg border-slate-300 text-sm" />
            <select wire:model="scenarioFlowName" class="rounded-lg border-slate-300 text-sm">
                <option value="integration_backbone">Integration backbone</option>
                <option value="platform_payments">Platform payments</option>
                <option value="platform_recovery">Platform recovery</option>
                <option value="platform_analytics">Platform analytics</option>
                <option value="production_observability">Production observability</option>
            </select>
            <select wire:model="scenarioEnvironment" class="rounded-lg border-slate-300 text-sm">
                <option value="staging">Staging</option>
                <option value="production">Production</option>
            </select>
            <input wire:model="requestBudget" type="number" placeholder="Budget" class="rounded-lg border-slate-300 text-sm" />
            <input wire:model="durationSeconds" type="number" placeholder="Duracao" class="rounded-lg border-slate-300 text-sm" />
            <input wire:model="concurrencyLevel" type="number" placeholder="Concorrencia" class="rounded-lg border-slate-300 text-sm" />
            <input wire:model="expectedThroughputPerMinute" type="number" placeholder="Throughput esperado" class="rounded-lg border-slate-300 text-sm" />
            <input wire:model="expectedP95LatencyMs" type="number" placeholder="P95 esperado" class="rounded-lg border-slate-300 text-sm" />
            <input wire:model="expectedErrorRate" type="number" step="0.0001" placeholder="Erro esperado" class="rounded-lg border-slate-300 text-sm" />
        </div>
        <button wire:click="registerScenario" class="mt-4 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
            Registrar cenario
        </button>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Registrar benchmark</h3>
        <div class="mt-4 grid gap-3 md:grid-cols-4">
            <select wire:model="selectedScenarioId" class="rounded-lg border-slate-300 text-sm">
                <option value="0">Selecione um cenario</option>
                @foreach ($scenarios as $scenario)
                    <option value="{{ $scenario['id'] }}">{{ $scenario['scenario_name'] }} / {{ $scenario['environment'] }}</option>
                @endforeach
            </select>
            <input wire:model="throughputPerMinute" type="number" placeholder="Throughput medido" class="rounded-lg border-slate-300 text-sm" />
            <input wire:model="p95LatencyMs" type="number" placeholder="P95 medido" class="rounded-lg border-slate-300 text-sm" />
            <input wire:model="errorRate" type="number" step="0.0001" placeholder="Erro medido" class="rounded-lg border-slate-300 text-sm" />
        </div>
        <button wire:click="recordExecution" class="mt-4 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">
            Registrar benchmark
        </button>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Registrar gargalo</h3>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <select wire:model="selectedExecutionId" class="rounded-lg border-slate-300 text-sm">
                <option value="0">Selecione um benchmark</option>
                @foreach ($executions as $execution)
                    <option value="{{ $execution['id'] }}">{{ $execution['scenario_name'] }} / {{ $execution['comparison_status'] }}</option>
                @endforeach
            </select>
            <select wire:model="bottleneckFlowName" class="rounded-lg border-slate-300 text-sm">
                <option value="integration_backbone">Integration backbone</option>
                <option value="platform_payments">Platform payments</option>
                <option value="platform_recovery">Platform recovery</option>
                <option value="platform_analytics">Platform analytics</option>
                <option value="production_observability">Production observability</option>
            </select>
            <select wire:model="bottleneckCategory" class="rounded-lg border-slate-300 text-sm">
                <option value="database">Database</option>
                <option value="queue">Queue</option>
                <option value="external_endpoint">External endpoint</option>
                <option value="application">Application</option>
            </select>
            <input wire:model="componentName" type="text" placeholder="Componente" class="rounded-lg border-slate-300 text-sm" />
            <input wire:model="impactLevel" type="text" placeholder="Impacto" class="rounded-lg border-slate-300 text-sm" />
            <input wire:model="bottleneckSummary" type="text" placeholder="Resumo do gargalo" class="rounded-lg border-slate-300 text-sm" />
        </div>
        <button wire:click="recordBottleneck" class="mt-4 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">
            Registrar gargalo
        </button>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Execucoes recentes</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Cenario</th>
                        <th class="pb-2 pr-4">Fluxo</th>
                        <th class="pb-2 pr-4">Throughput</th>
                        <th class="pb-2 pr-4">P95</th>
                        <th class="pb-2 pr-4">Erro</th>
                        <th class="pb-2 pr-4">Comparacao</th>
                        <th class="pb-2 pr-4">Acao</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @forelse ($executions as $execution)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $execution['scenario_name'] }}</td>
                            <td class="py-2 pr-4">{{ $execution['flow_name'] }}</td>
                            <td class="py-2 pr-4">{{ $execution['throughput_per_minute'] }}</td>
                            <td class="py-2 pr-4">{{ $execution['p95_latency_ms'] }} ms</td>
                            <td class="py-2 pr-4">{{ number_format($execution['error_rate'] * 100, 2) }}%</td>
                            <td class="py-2 pr-4">{{ $execution['comparison_status'] }}</td>
                            <td class="py-2 pr-4">
                                <button wire:click="promoteBaseline({{ $execution['id'] }})" class="rounded-lg border border-emerald-300 px-3 py-1 text-xs font-medium text-emerald-700">
                                    Promover baseline
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-slate-500">Nenhum benchmark registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Gargalos registrados</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Fluxo</th>
                        <th class="pb-2 pr-4">Categoria</th>
                        <th class="pb-2 pr-4">Componente</th>
                        <th class="pb-2 pr-4">Impacto</th>
                        <th class="pb-2 pr-4">Resumo</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @forelse ($bottlenecks as $bottleneck)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $bottleneck['flow_name'] }}</td>
                            <td class="py-2 pr-4">{{ $bottleneck['category'] }}</td>
                            <td class="py-2 pr-4">{{ $bottleneck['component_name'] }}</td>
                            <td class="py-2 pr-4">{{ $bottleneck['impact_level'] }}</td>
                            <td class="py-2 pr-4">{{ $bottleneck['summary'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-slate-500">Nenhum gargalo registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Registrar tuning</h3>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <input wire:model="changeKey" type="text" placeholder="Change key" class="rounded-lg border-slate-300 text-sm" />
            <input wire:model="hypothesisSummary" type="text" placeholder="Hipotese de tuning" class="rounded-lg border-slate-300 text-sm" />
            <select wire:model="changeType" class="rounded-lg border-slate-300 text-sm">
                <option value="index">Index</option>
                <option value="query_rewrite">Query rewrite</option>
                <option value="queue_tuning">Queue tuning</option>
            </select>
        </div>
        <button wire:click="registerTuningChange" class="mt-4 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">
            Registrar tuning
        </button>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Tuning changes</h3>
        <div class="mt-4">
            <input wire:model="selectedValidationExecutionId" type="number" placeholder="Benchmark de validacao" class="rounded-lg border-slate-300 text-sm" />
        </div>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Change key</th>
                        <th class="pb-2 pr-4">Fluxo</th>
                        <th class="pb-2 pr-4">Status</th>
                        <th class="pb-2 pr-4">Rollback</th>
                        <th class="pb-2 pr-4">Acoes</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @forelse ($tuningChanges as $change)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $change['change_key'] }}</td>
                            <td class="py-2 pr-4">{{ $change['flow_name'] }}</td>
                            <td class="py-2 pr-4">{{ $change['status'] }}</td>
                            <td class="py-2 pr-4">{{ $change['rollback_recommended'] ? 'sim' : 'nao' }}</td>
                            <td class="py-2 pr-4">
                                <div class="flex flex-col gap-2 lg:flex-row">
                                    @if ($change['status'] === 'pending')
                                        <button wire:click="validateTuning({{ $change['id'] }})" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700">
                                            Validar
                                        </button>
                                    @endif
                                    @if ($change['status'] === 'validated' && ! $change['rollback_recommended'])
                                        <button wire:click="promoteTuning({{ $change['id'] }})" class="rounded-lg border border-emerald-300 px-3 py-1 text-xs font-medium text-emerald-700">
                                            Promover
                                        </button>
                                    @endif
                                    @if ($change['rollback_recommended'])
                                        <button wire:click="rollbackTuning({{ $change['id'] }})" class="rounded-lg border border-rose-300 px-3 py-1 text-xs font-medium text-rose-700">
                                            Rollback
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-slate-500">Nenhum tuning registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Rollback evidences</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Change key</th>
                        <th class="pb-2 pr-4">Fluxo</th>
                        <th class="pb-2 pr-4">Resultado</th>
                        <th class="pb-2 pr-4">Operador</th>
                        <th class="pb-2 pr-4">Motivo</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @forelse ($rollbackEvidences as $evidence)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $evidence['change_key'] }}</td>
                            <td class="py-2 pr-4">{{ $evidence['flow_name'] }}</td>
                            <td class="py-2 pr-4">{{ $evidence['result_status'] }}</td>
                            <td class="py-2 pr-4">{{ $evidence['operator'] ?? 'sistema' }}</td>
                            <td class="py-2 pr-4">{{ $evidence['rollback_reason'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-slate-500">Nenhuma evidencia de rollback registrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
