<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Historico de vales</h3>
            <p class="mt-1 text-sm text-slate-500">Filtre por status e cliente para acompanhar vendas e atendimentos.</p>
        </div>
        <div class="grid gap-3 md:grid-cols-2">
            <input type="text" wire:model.live="search" placeholder="Buscar cliente" class="rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <select wire:model.live="status" class="rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todos os status</option>
                <option value="aberto">Aberto</option>
                <option value="faturado">Faturado</option>
                <option value="em_servico">Em servico</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Vale</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($vales as $vale)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">#{{ $vale->id }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $vale->cliente->razao_social }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ ucfirst($vale->status) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900">R$ {{ number_format($vale->valor_total, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Nenhum vale encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
