<div class="px-4 md:px-6 py-6 max-w-7xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Lado Esquerdo: Novo Vale e Pesquisa/Inclusão -->
        <div class="lg:col-span-2 space-y-6">
            
            @if(session()->has('success'))
                <div class="p-4 bg-green-100 text-green-700 rounded-md shadow-sm border-l-4 border-green-500 font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @error('geral')
                <div class="p-4 bg-red-100 text-red-700 rounded-md shadow-sm border-l-4 border-red-500 font-medium">
                    {{ $message }}
                </div>
            @enderror

            <!-- Header do Vale -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4">Dados do Ticket (Vale)</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-label for="clienteId" value="Cliente" />
                        <select id="clienteId" wire:model.live="clienteId" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" {{ $valeId ? 'disabled' : '' }}>
                            <option value="">Selecione o Cliente...</option>
                            @foreach($clientes as $cli)
                                <option value="{{ $cli->id }}">{{ $cli->nome }}</option>
                            @endforeach
                        </select>
                        @error('clienteId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-label for="depositoId" value="Depósito de Retirada" />
                        <select id="depositoId" wire:model="depositoId" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" {{ $valeId ? 'disabled' : '' }}>
                            @foreach($depositos as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($valeId)
                <div class="mt-4 pt-4 border-t flex justify-between items-center text-sm text-gray-600">
                    <span class="font-bold text-indigo-700">Vale Aberto: #{{ str_pad($valeId, 5, '0', STR_PAD_LEFT) }}</span>
                    <span>Vendedor: {{ auth()->user()->name ?? 'Logado' }}</span>
                </div>
                @endif
            </div>

            <!-- Pesquisa e Adição de Items -->
            @if($valeId)
            <div class="bg-gray-50 border border-gray-200 p-6 rounded-lg shadow-inner">
                <x-label for="searchSku" value="Adicionar Produto (Bateria)" class="text-indigo-800 font-bold" />
                <div class="relative mt-2">
                    <div class="flex">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <x-input id="searchSku" wire:model.live.debounce.300ms="searchSku" placeholder="Busque por SKU, Código ou Marca..." class="pl-10 block w-full border-gray-300 rounded-md" autocomplete="off" />
                        </div>
                    </div>

                    @if(count($bateriasEncontradas) > 0)
                        <div class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200 divide-y divide-gray-100 max-h-60 overflow-auto">
                            @foreach($bateriasEncontradas as $bat)
                                <div wire:click="adicionarItem({{ $bat->id }})" class="p-3 hover:bg-indigo-50 cursor-pointer flex justify-between items-center transition-colors">
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $bat->sku }} - {{ $bat->marca }}</p>
                                        <p class="text-xs text-gray-500">{{ $bat->tecnologia }} | {{ $bat->amperagem ?? 'N/A' }}Ah | {{ $bat->polo ?? 'N/A' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-green-600">R$ {{ number_format($bat->preco_venda, 2, ',', '.') }}</div>
                                        <div class="text-xs text-indigo-500 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            Adicionar
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Carrinho Itens -->
            @if($vale && count($vale->itens) > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Item / SKU</th>
                            <th class="px-4 py-3 text-center">Sucata na Troca?</th>
                            <th class="px-4 py-3 text-right">Preço Un.</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($vale->itens as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $item->bateria->marca }}</div>
                                <div class="text-xs text-gray-500 flex items-center justify-between gap-4">
                                    <span>Ref: {{ $item->bateria->sku }}</span>
                                    <input type="text" 
                                        wire:blur="updateSerialNumber({{ $item->id }}, $event.target.value)"
                                        value="{{ $item->numero_serie }}"
                                        class="text-[10px] py-1 px-2 border-gray-100 bg-gray-50/50 rounded flex-1 focus:ring-1 focus:ring-indigo-500" 
                                        placeholder="Nº de Série (Opcional)">
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:click="toggleSucata({{ $item->id }})" class="sr-only peer" {{ $item->flag_devolveu_sucata ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                </label>
                                <div class="text-[10px] text-gray-500 mt-1">{{ $item->flag_devolveu_sucata ? 'Tirou casco' : 'S/ Casco (+R$)' }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="font-mono text-sm {{ $item->flag_devolveu_sucata ? 'text-gray-900' : 'text-red-600 font-bold' }}">
                                    R$ {{ number_format($item->preco_unitario_final, 2, ',', '.') }}
                                </div>
                                @if(!$item->flag_devolveu_sucata)
                                    <div class="text-[10px] text-gray-400 line-through">Base: {{ number_format($item->preco_unitario_original, 2, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-indigo-700 font-mono">
                                R$ {{ number_format($item->quantidade * $item->preco_unitario_final, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="removerItem({{ $item->id }})" wire:confirm="Isso estornará a reserva no estoque. Confirmar?" class="text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Lado Direito: Fechamento / Resumo -->
        @if($valeId)
        <div>
            <div class="bg-gray-800 text-white p-6 rounded-lg shadow-lg sticky top-6">
                <h3 class="text-lg font-medium border-b border-gray-700 pb-3">Resumo da Venda</h3>
                
                <div class="mt-6 flex justify-between items-end">
                    <span class="text-gray-400 text-sm uppercase font-semibold">Total Liquido</span>
                    <span class="text-3xl font-bold text-green-400 font-mono">R$ {{ number_format($totalGeral, 2, ',', '.') }}</span>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-700 text-xs text-gray-400 space-y-2">
                    <p>✓ As baterias inclusas aqui <b>já constam reservadas</b> no sistema impedindo duplicidade.</p>
                    <p>✓ Clicar na chave <i>"Sucata na Troca?"</i> recalcula os acréscimos parametrizados no Cadastro do Produto instantaneamente.</p>
                </div>

                @if(count($vale->itens) > 0)
                <div class="mt-8 space-y-3">
                    <button wire:click="$dispatch('openConverseVale', { type: 'pedido', valeId: {{ $vale->id }} })" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded transition-colors shadow flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Faturar / Gerar Pedido
                    </button>
                    
                    <button wire:click="$dispatch('openConverseVale', { type: 'os', valeId: {{ $vale->id }} })" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded transition-colors shadow flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Ordem de Serviço (OS)
                    </button>

                    <button wire:click="cancelarVale" wire:confirm="Tem certeza que deseja cancelar esta venda? O estoque será imediatamente disponibilizado aos demais vendedores." class="w-full mt-4 bg-transparent border border-red-500 text-red-400 hover:bg-red-500 hover:text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Cancelar e Estornar
                    </button>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- The actual modal logic could go here or within another nested component for closing jobs. -->
    <livewire:vale-conversor />
</div>
