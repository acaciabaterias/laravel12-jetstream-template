<div class="p-6 bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="flex justify-between items-end mb-6 border-b pb-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Painel Geral - Conta Sucata</h2>
            <p class="text-sm text-gray-500 mt-1">Gestão do passivo ambiental e devolução logística de cascos usados.</p>
        </div>
        <div>
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <button type="button" wire:click="$set('viewType', 'fornecedores')" class="px-4 py-2 text-sm font-medium {{ $viewType === 'fornecedores' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-200 rounded-l-lg focus:z-10 focus:ring-2 focus:ring-indigo-500">
                    Saldos c/ Fornecedores
                </button>
                <button type="button" wire:click="$set('viewType', 'clientes')" class="px-4 py-2 text-sm font-medium {{ $viewType === 'clientes' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-200 rounded-r-md focus:z-10 focus:ring-2 focus:ring-indigo-500">
                    Saldos c/ Clientes
                </button>
            </div>
        </div>
    </div>

    @php
        $records = $viewType === 'fornecedores' ? $fornecedores : $clientes;
    @endphp

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entidade</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doc (CNPJ/CPF)</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Físico (KG)</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Passivo Financeiro Estimado</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($records as $rec)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">{{ $rec->nome }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $rec->cnpj ?? $rec->cpf_cnpj ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-sm">
                            @if($rec->saldo_sucata_kg < 0)
                                <span class="text-red-600 font-bold">{{ number_format($rec->saldo_sucata_kg, 2, ',', '.') }} kg</span>
                                <span class="text-xs text-red-400 block>(Nós devemos à eles)</span>
                            @else
                                <span class="text-green-600 font-bold">{{ number_format($rec->saldo_sucata_kg, 2, ',', '.') }} kg</span>
                                <span class="text-xs text-green-400 block>(Eles devem à nós)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-sm">
                            @if($rec->saldo_sucata_financeiro < 0)
                                <span class="text-red-600 font-bold">R$ {{ number_format(abs($rec->saldo_sucata_financeiro), 2, ',', '.') }}</span>
                            @else
                                <span class="text-green-600 font-bold">R$ {{ number_format($rec->saldo_sucata_financeiro, 2, ',', '.') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 whitespace-nowrap text-sm text-gray-500 text-center">
                            <svg class="mx-auto h-12 w-12 text-green-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.514" />
                            </svg>
                            Todos os saldos de logística reversa e retenção de sucata estão zerados para esta Filial.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
