<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 border-b pb-4">
        <h2 class="text-xl font-semibold text-gray-800">Saldos de Estoque</h2>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 w-full md:w-auto">
            <select wire:model.live="filtroDeposito" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                <option value="">Todos os Depósitos</option>
                @foreach($depositos as $dep)
                    <option value="{{ $dep->id }}">{{ $dep->nome }}</option>
                @endforeach
            </select>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por SKU ou Marca..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Depósito</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU / Produto</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Disponível</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($saldos as $saldo)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ $saldo->deposito->nome ?? 'Desconhecido' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="font-bold text-indigo-600">{{ $saldo->bateria->sku }}</span>
                            - {{ $saldo->bateria->marca }} 
                            <span class="text-xs text-gray-400">({{ $saldo->bateria->tecnologia }})</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($saldo->quantidade_atual <= 2)
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full bg-red-100 text-red-800">
                                    {{ $saldo->quantidade_atual }}
                                </span>
                            @elseif($saldo->quantidade_atual <= 5)
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ $saldo->quantidade_atual }}
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full bg-green-100 text-green-800">
                                    {{ $saldo->quantidade_atual }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center">Nenhum saldo encontrado para os filtros atuais.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($saldos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $saldos->links() }}
            </div>
        @endif
    </div>
</div>
