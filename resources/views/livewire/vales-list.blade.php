<div class="p-6 bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 border-b pb-4 gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Histórico de Vales</h2>
            <p class="text-sm text-gray-500 mt-1">Todos os tickets de venda abertos e fechados desta filial.</p>
        </div>
        <a href="/pdv" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-md shadow hover:bg-indigo-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nova Venda
        </a>
    </div>

    <!-- Filtros -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
        <div>
            <x-label for="searchCliente" value="Cliente" class="text-xs" />
            <x-input id="searchCliente" wire:model.live.debounce.300ms="searchCliente" placeholder="Nome do cliente..." class="mt-1 block w-full h-9 text-sm" />
        </div>
        <div>
            <x-label for="filterStatus" value="Status" class="text-xs" />
            <select id="filterStatus" wire:model.live="filterStatus" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm h-9 text-sm">
                <option value="">Todos</option>
                <option value="aberto">Abertos</option>
                <option value="faturado">Faturados</option>
                <option value="em_os">Em OS</option>
                <option value="cancelado">Cancelados</option>
            </select>
        </div>
        <div>
            <x-label for="dataDe" value="Data Início" class="text-xs" />
            <x-input id="dataDe" wire:model.live="dataDe" type="date" class="mt-1 block w-full h-9 text-sm" />
        </div>
        <div>
            <x-label for="dataAte" value="Data Fim" class="text-xs" />
            <x-input id="dataAte" wire:model.live="dataAte" type="date" class="mt-1 block w-full h-9 text-sm" />
        </div>
        <div class="flex items-end">
            <button wire:click="$set('searchCliente', ''), $set('filterStatus', ''), $set('dataDe', ''), $set('dataAte', '')" class="w-full border border-gray-300 text-gray-600 text-sm rounded-md h-9 hover:bg-gray-100 transition-colors">
                Limpar Filtros
            </button>
        </div>
    </div>

    <!-- Tabela -->
    <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Cliente</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Vendedor</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Itens</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Criado Em</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($vales as $vale)
                    @php
                        $totalVale = $vale->itens->sum(fn($i) => $i->quantidade * $i->preco_unitario_final);
                        $statusMap = [
                            'aberto' => 'bg-blue-100 text-blue-800',
                            'faturado' => 'bg-green-100 text-green-800',
                            'em_os' => 'bg-yellow-100 text-yellow-800',
                            'cancelado' => 'bg-red-100 text-red-800',
                        ];
                        $badgeClass = $statusMap[$vale->status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-mono text-gray-500">#{{ str_pad($vale->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $vale->cliente->nome ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $vale->vendedor->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $vale->itens->count() }}</td>
                        <td class="px-4 py-3 text-right font-mono font-semibold text-gray-800">
                            R$ {{ number_format($totalVale, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {{ ucfirst($vale->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $vale->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($vale->status === 'aberto')
                                <a href="/pdv/{{ $vale->id }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">Continuar →</a>
                            @else
                                <a href="/vales/{{ $vale->id }}" class="text-gray-500 hover:text-gray-700 font-medium text-xs">Ver →</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                            <svg class="mx-auto h-12 w-12 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Nenhum Vale encontrado com os filtros aplicados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $vales->links() }}
    </div>
</div>
