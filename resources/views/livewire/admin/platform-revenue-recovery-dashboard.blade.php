<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Saúde da recuperação de receita</h2>
        <p class="mt-1 text-sm text-slate-600">Acompanhe backlog, escalonamentos, promessas e recuperação da carteira SaaS.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Casos abertos</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['open_cases'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Casos pausados</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['paused_cases'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Escalonados</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['escalated_cases'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Recuperados</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['recovered_cases'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Exposição prometida</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">R$ {{ number_format((float) $summary['promised_exposure'], 2, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar cliente ou estágio" class="rounded-lg border-slate-300 text-sm">
        <select wire:model.live="statusFilter" class="rounded-lg border-slate-300 text-sm">
            <option value="all">Todos os status</option>
            <option value="open">Open</option>
            <option value="paused">Paused</option>
            <option value="escalated">Escalated</option>
            <option value="recovered">Recovered</option>
        </select>
        <select wire:model.live="stageFilter" class="rounded-lg border-slate-300 text-sm">
            <option value="all">Todos os estágios</option>
            <option value="d1">d1</option>
            <option value="d3">d3</option>
            <option value="escalated">escalated</option>
        </select>
        <select wire:model.live="severityFilter" class="rounded-lg border-slate-300 text-sm">
            <option value="all">Todas as severidades</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="critical">Critical</option>
        </select>
        <input wire:model.live="ownerFilter" type="text" placeholder="ID do responsável" class="rounded-lg border-slate-300 text-sm">
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Caso</th>
                        <th class="pb-2 pr-4">Cliente</th>
                        <th class="pb-2 pr-4">Estágio</th>
                        <th class="pb-2 pr-4">Status</th>
                        <th class="pb-2 pr-4">Severidade</th>
                        <th class="pb-2 pr-4">Responsável</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @foreach ($cases as $case)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">#{{ $case->id }}</td>
                            <td class="py-2 pr-4">{{ $case->cliente->razao_social ?? $case->cliente->nome_fantasia ?? 'Cliente' }}</td>
                            <td class="py-2 pr-4">{{ $case->current_stage }}</td>
                            <td class="py-2 pr-4">{{ $case->status->value }}</td>
                            <td class="py-2 pr-4">{{ $case->severity->value }}</td>
                            <td class="py-2 pr-4">{{ $case->owner?->name ?? 'Automático' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $cases->links() }}
        </div>
    </div>
</div>
