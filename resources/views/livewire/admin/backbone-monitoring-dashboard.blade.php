<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Monitoring readiness</h2>
                <p class="mt-1 text-sm text-slate-600">Acompanhe scrape health, disponibilidade de coletores e readiness do stack externo.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <button wire:click="evaluateAlerts" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">
                    Avaliar alertas
                </button>
                <button wire:click="rebuild" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                    Reavaliar targets
                </button>
            </div>
        </div>
    </div>

    @if ($operationMessage)
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ $operationMessage }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">Healthy</div><div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['healthy'] }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">Degraded</div><div class="mt-2 text-2xl font-semibold text-amber-700">{{ $summary['degraded'] }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">Unavailable</div><div class="mt-2 text-2xl font-semibold text-rose-700">{{ $summary['unavailable'] }}</div></div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-3">
            <select wire:model.live="flowNameFilter" class="rounded-lg border-slate-300 text-sm">
                <option value="">Todos os fluxos</option>
                <option value="integration_backbone">Integration backbone</option>
                <option value="platform_payments">Platform payments</option>
                <option value="platform_recovery">Platform recovery</option>
                <option value="platform_analytics">Platform analytics</option>
                <option value="production_observability">Production observability</option>
            </select>
            <select wire:model.live="severityFilter" class="rounded-lg border-slate-300 text-sm">
                <option value="">Todas as severidades</option>
                <option value="warning">Warning</option>
                <option value="critical">Critical</option>
            </select>
            <select wire:model.live="alertStatusFilter" class="rounded-lg border-slate-300 text-sm">
                <option value="">Todos os alertas</option>
                <option value="triggered">Triggered</option>
                <option value="clear">Clear</option>
                <option value="unknown">Unknown</option>
                <option value="inactive">Inactive</option>
            </select>
            <select wire:model.live="environmentFilter" class="rounded-lg border-slate-300 text-sm">
                <option value="">Todos os ambientes</option>
                <option value="staging">Staging</option>
                <option value="production">Production</option>
            </select>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Targets monitorados</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Target</th>
                        <th class="pb-2 pr-4">Fluxo</th>
                        <th class="pb-2 pr-4">Ambiente</th>
                        <th class="pb-2 pr-4">Status</th>
                        <th class="pb-2 pr-4">Latencia</th>
                        <th class="pb-2 pr-4">Samples</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @forelse ($targets as $target)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $target->target_name }}</td>
                            <td class="py-2 pr-4">{{ $target->flow_name }}</td>
                            <td class="py-2 pr-4">{{ $target->environment }}</td>
                            <td class="py-2 pr-4">{{ $target->status->value }}</td>
                            <td class="py-2 pr-4">{{ $target->latestProbeSnapshot?->latency_ms ?? 0 }} ms</td>
                            <td class="py-2 pr-4">{{ $target->latestProbeSnapshot?->sample_count ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-slate-500">Nenhum target monitorado cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Regras de alerta</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Regra</th>
                        <th class="pb-2 pr-4">Fluxo</th>
                        <th class="pb-2 pr-4">Severidade</th>
                        <th class="pb-2 pr-4">Status</th>
                        <th class="pb-2 pr-4">Targets impactados</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @forelse ($alertRules as $rule)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">
                                <div class="font-medium text-slate-900">{{ $rule['rule_name'] }}</div>
                                <div class="text-xs text-slate-500">{{ $rule['condition_summary'] }}</div>
                            </td>
                            <td class="py-2 pr-4">{{ $rule['flow_name'] }}</td>
                            <td class="py-2 pr-4">{{ $rule['severity'] }}</td>
                            <td class="py-2 pr-4">{{ $rule['alert_status'] }}</td>
                            <td class="py-2 pr-4">
                                @if ($rule['matched_targets'] === [])
                                    <span class="text-slate-400">Sem materializacao</span>
                                @else
                                    {{ collect($rule['matched_targets'])->pluck('target_name')->implode(', ') }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-slate-500">Nenhuma regra de alerta encontrada para os filtros atuais.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
            <div class="grid flex-1 gap-3 md:grid-cols-3">
                <input wire:model="packageName" type="text" placeholder="Pacote" class="rounded-lg border-slate-300 text-sm" />
                <input wire:model="packageVersion" type="text" placeholder="Versao" class="rounded-lg border-slate-300 text-sm" />
                <select wire:model="packageEnvironment" class="rounded-lg border-slate-300 text-sm">
                    <option value="staging">Staging</option>
                    <option value="production">Production</option>
                </select>
            </div>
            <button wire:click="registerPackage" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                Registrar pacote
            </button>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Provisionamento de dashboards</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Pacote</th>
                        <th class="pb-2 pr-4">Ambiente</th>
                        <th class="pb-2 pr-4">Status</th>
                        <th class="pb-2 pr-4">Validacao</th>
                        <th class="pb-2 pr-4">Operacoes</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @forelse ($provisioningRecords as $record)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">
                                <div class="font-medium text-slate-900">{{ $record['package_name'] }}</div>
                                <div class="text-xs text-slate-500">{{ $record['version'] }}</div>
                            </td>
                            <td class="py-2 pr-4">{{ $record['environment'] }}</td>
                            <td class="py-2 pr-4">{{ $record['status'] }}</td>
                            <td class="py-2 pr-4">{{ $record['validated_at'] ?? 'pendente' }}</td>
                            <td class="py-2 pr-4">
                                <div class="flex flex-col gap-2 lg:flex-row">
                                    @if ($record['status'] === 'pending')
                                        <button wire:click="applyPackage({{ $record['id'] }})" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700">
                                            Aplicar
                                        </button>
                                    @endif
                                    @if ($record['status'] === 'applied')
                                        <button wire:click="validatePackage({{ $record['id'] }})" class="rounded-lg border border-emerald-300 px-3 py-1 text-xs font-medium text-emerald-700">
                                            Validar
                                        </button>
                                        <button wire:click="rollbackPackage({{ $record['id'] }})" class="rounded-lg border border-rose-300 px-3 py-1 text-xs font-medium text-rose-700">
                                            Rollback
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-slate-500">Nenhum pacote de dashboard registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <input wire:model="rollbackVersion" type="text" placeholder="Versao de rollback para a proxima operacao" class="w-full rounded-lg border-slate-300 text-sm" />
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Evidencias de readiness</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Tipo</th>
                        <th class="pb-2 pr-4">Ambiente</th>
                        <th class="pb-2 pr-4">Resultado</th>
                        <th class="pb-2 pr-4">Operador</th>
                        <th class="pb-2 pr-4">Registro</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @forelse ($readinessEvidences as $evidence)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $evidence['evidence_type'] }}</td>
                            <td class="py-2 pr-4">{{ $evidence['environment'] }}</td>
                            <td class="py-2 pr-4">{{ $evidence['result_status'] }}</td>
                            <td class="py-2 pr-4">{{ $evidence['operator'] ?? 'sistema' }}</td>
                            <td class="py-2 pr-4">{{ $evidence['recorded_at'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-slate-500">Nenhuma evidencia de readiness registrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
