<div class="rounded-lg border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/60 lg:p-6">
    <div class="mb-5 flex flex-col gap-4 border-b border-slate-200 pb-5 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <div class="inline-flex rounded-lg border border-[#123b66]/15 bg-[#123b66]/5 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#123b66]">
                Central de vales
            </div>
            <h3 class="mt-4 text-2xl font-semibold tracking-tight text-slate-950">Listagem operacional de Vales</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Use filtros de status, período e cliente para localizar rapidamente um atendimento e aplicar ações comerciais.</p>
        </div>

        <div class="grid w-full gap-3 md:grid-cols-3 xl:max-w-3xl">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cliente ou fantasia" class="rounded-lg border-slate-200 bg-slate-50 shadow-sm focus:border-[#123b66] focus:ring-[#123b66]">
            <select wire:model.live="status" class="rounded-lg border-slate-200 bg-slate-50 shadow-sm focus:border-[#123b66] focus:ring-[#123b66]">
                <option value="">Todos os status</option>
                <option value="aberto">Aberto</option>
                <option value="faturado">Faturado</option>
                <option value="em_servico">Em serviço</option>
                <option value="cancelado">Cancelado</option>
            </select>
            <select wire:model.live="periodo" class="rounded-lg border-slate-200 bg-slate-50 shadow-sm focus:border-[#123b66] focus:ring-[#123b66]">
                <option value="hoje">Hoje</option>
                <option value="7_dias">Últimos 7 dias</option>
                <option value="30_dias">Últimos 30 dias</option>
                <option value="90_dias">Últimos 90 dias</option>
            </select>
        </div>
    </div>

    @error('vale')
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $message }}</div>
    @enderror

    @if (session()->has('vale-feedback'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('vale-feedback') }}</div>
    @endif

    <div class="overflow-x-auto rounded-lg border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Vale</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Período</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($vales as $vale)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-4 text-sm font-medium text-slate-900">#{{ $vale->id }}</td>
                        <td class="px-4 py-4 text-sm text-slate-600">
                            <p class="font-medium text-slate-900">{{ $vale->cliente->razao_social }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-400">{{ $vale->cliente->nome_fantasia ?: 'Sem fantasia' }}</p>
                        </td>
                        <td class="px-4 py-4 text-sm text-slate-600">
                            <span class="rounded-lg px-3 py-1 text-xs font-semibold {{ $vale->status === 'aberto' ? 'bg-[#123b66]/10 text-[#123b66]' : ($vale->status === 'faturado' ? 'bg-emerald-100 text-emerald-700' : ($vale->status === 'cancelado' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600')) }}">
                                {{ ucfirst($vale->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm text-slate-600">{{ optional($vale->data_criacao)->format('d/m/Y') }}</td>
                        <td class="px-4 py-4 text-right text-sm font-semibold text-slate-900">R$ {{ number_format($vale->valor_total, 2, ',', '.') }}</td>
                        <td class="px-4 py-4">
                            <div class="flex justify-end gap-2">
                                <button type="button" wire:click="viewVale({{ $vale->id }})" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#123b66] hover:text-[#123b66]">
                                    Visualizar
                                </button>
                                <button type="button" wire:click="cancelVale({{ $vale->id }})" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-50" @disabled($vale->status !== 'aberto')>
                                    Cancelar
                                </button>
                                <button type="button" wire:click="faturarVale({{ $vale->id }})" class="rounded-lg bg-[#f59e0b] px-3 py-2 text-xs font-semibold text-slate-950 transition hover:bg-[#e69008] disabled:cursor-not-allowed disabled:opacity-50" @disabled($vale->status !== 'aberto')>
                                    Faturar
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">Nenhum vale encontrado para os filtros informados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
