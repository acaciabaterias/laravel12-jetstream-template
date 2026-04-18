<div class="px-4 md:px-6 py-8 max-w-7xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Coluna de Configuração da Rota -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                    Configurar Rota
                </h2>

                <div class="space-y-4">
                    <div>
                        <x-label for="entregadorId" value="Entregador" class="text-xs font-semibold uppercase tracking-wider text-gray-500" />
                        <select id="entregadorId" wire:model="entregadorId" class="mt-1 block w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm h-11 transition-all">
                            <option value="">Selecione o motorista...</option>
                            @foreach($entregadores as $ent)
                                <option value="{{ $ent->id }}">{{ $ent->name }}</option>
                            @endforeach
                        </select>
                        @error('entregadorId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-label for="dataRota" value="Data da Rota" class="text-xs font-semibold uppercase tracking-wider text-gray-500" />
                        <x-input id="dataRota" type="date" wire:model="dataRota" class="mt-1 block w-full border-gray-200 rounded-xl h-11" />
                        @error('dataRota') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-label for="veiculo" value="Veículo / Placa" class="text-xs font-semibold uppercase tracking-wider text-gray-500" />
                        <x-input id="veiculo" wire:model="veiculo" placeholder="Ex: Caminhão 01 - ABC-1234" class="mt-1 block w-full border-gray-200 rounded-xl h-11" />
                    </div>

                    <div>
                        <x-label for="observacoes" value="Observações da Carga" class="text-xs font-semibold uppercase tracking-wider text-gray-500" />
                        <textarea id="observacoes" wire:model="observacoes" rows="3" class="mt-1 block w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition-all" placeholder="Instruções para o entregador..."></textarea>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-50">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm font-medium text-gray-600">Paradas Selecionadas:</span>
                        <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">{{ count($valeIdsSelecionados) }}</span>
                    </div>

                    <button wire:click="criarRota" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-indigo-100 transition-all flex justify-center items-center gap-2 group {{ count($valeIdsSelecionados) === 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ count($valeIdsSelecionados) === 0 ? 'disabled' : '' }}>
                        Gerar Romaneio
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                    @error('geral') <p class="mt-2 text-center text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            @if(session()->has('success'))
                <div class="p-4 bg-green-50 text-green-700 rounded-xl border border-green-100 shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif
        </div>

        <!-- Coluna de Seleção de Vales -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Vales pendentes de entrega</h3>
                    <p class="text-sm text-gray-500">Selecione os tickets que serão carregados para esta rota.</p>
                </div>
                <button wire:click="carregarValesDisponiveis" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($valesDisponiveis as $vale)
                    @php 
                        $selecionado = in_array($vale->id, $valeIdsSelecionados);
                        $total = $vale->itens->sum(fn($i) => $i->quantidade * $i->preco_unitario_final);
                    @endphp
                    <div wire:click="toggleVale({{ $vale->id }})" class="relative bg-white p-5 rounded-2xl border-2 transition-all cursor-pointer hover:shadow-md {{ $selecionado ? 'border-indigo-500 ring-4 ring-indigo-50' : 'border-gray-100' }}">
                        @if($selecionado)
                            <div class="absolute -top-3 -right-3 bg-indigo-600 text-white rounded-full p-1 opacity-100 shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        @endif

                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Vale #{{ str_pad($vale->id, 5, '0', STR_PAD_LEFT) }}</span>
                                <h4 class="font-bold text-gray-900 line-clamp-1">{{ $vale->cliente->nome_fantasia ?: $vale->cliente->razao_social }}</h4>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $vale->status === 'em_os' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $vale->status }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-4">
                            @foreach($vale->itens->take(2) as $item)
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>{{ $item->quantidade }}x {{ $item->bateria->marca }}</span>
                                    <span class="font-mono">R$ {{ number_format($item->preco_unitario_final, 2, ',', '.') }}</span>
                                </div>
                            @endforeach
                            @if($vale->itens->count() > 2)
                                <div class="text-[10px] text-gray-400 italic font-medium mt-1">+ {{ $vale->itens->count() - 2 }} outros itens...</div>
                            @endif
                        </div>

                        <div class="flex justify-between items-center pt-3 border-t border-gray-50">
                            <span class="text-xs text-gray-400">{{ $vale->created_at->diffForHumans() }}</span>
                            <span class="text-lg font-bold text-indigo-700 font-mono">R$ {{ number_format($total, 2, ',', '.') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-2 py-20 text-center bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <h4 class="text-gray-900 font-bold">Nenhum vale disponível</h4>
                        <p class="text-gray-500 text-sm mt-1">Todos os pedidos já estão em rotas ou foram cancelados.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
