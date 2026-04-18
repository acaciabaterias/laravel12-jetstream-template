<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex justify-between items-center bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight italic">Garantias e Assistência</h1>
            <p class="text-sm text-gray-500 font-medium">Gestão de laudos técnicos e backup de baterias</p>
        </div>
        <button wire:click="$set('showModal', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-indigo-100 transition-all flex items-center gap-2 group">
            <svg class="w-5 h-5 group-hover:rotate-90 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nova Garantia
        </button>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filtros e Busca -->
    <div class="relative max-w-md">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-3 border-gray-100 bg-white rounded-2xl shadow-sm focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all text-sm" placeholder="Buscar por cliente...">
    </div>

    <!-- Tabela de O.S. -->
    <div class="bg-white rounded-[2rem] shadow-xl shadow-gray-200/50 overflow-hidden border border-gray-100">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">ID / Data</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Cliente / Bateria</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Status</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Resultado</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($garantias as $os)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-5">
                            <span class="text-xs font-bold text-indigo-600">#{{ str_pad($os->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <p class="text-[10px] text-gray-400 mt-0.5 font-mono">{{ $os->data_abertura->format('d/m/Y H:i') }}</p>
                        </td>
                        <td class="px-6 py-5">
                            <h4 class="text-sm font-bold text-gray-900 leading-none">{{ $os->cliente->nome_fantasia ?: $os->cliente->razao_social }}</h4>
                            <p class="text-xs text-gray-500 mt-1 italic">{{ $os->bateria->marca }} - {{ $os->bateria->amperagem }}Ah</p>
                        </td>
                        <td class="px-6 py-5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter
                                {{ $os->status === 'aberta' ? 'bg-blue-50 text-blue-600' : '' }}
                                {{ $os->status === 'em_avaliacao' ? 'bg-yellow-50 text-yellow-600' : '' }}
                                {{ $os->status === 'pronta' ? 'bg-green-50 text-green-600' : '' }}
                                {{ $os->status === 'negada' ? 'bg-red-50 text-red-600' : '' }}
                            ">
                                {{ str_replace('_', ' ', $os->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-5">
                            @if($os->resultado)
                                <span class="text-xs font-bold uppercase {{ $os->resultado === 'procedente' ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $os->resultado }}
                                </span>
                            @else
                                <span class="text-xs text-gray-300 italic">Aguardando laudo</span>
                            @endif
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-2">
                                <button class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                                <button class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 bg-gray-50/30">
            {{ $garantias->links() }}
        </div>
    </div>

    <!-- Modal de Nova Garantia -->
    <x-dialog-modal wire:model="showModal">
        <x-slot name="title">
            <h2 class="text-xl font-black text-gray-900 tracking-tight italic uppercase">Abrir O.S. de Garantia</h2>
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <x-label for="clienteId" value="Cliente" class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1" />
                    <select id="clienteId" wire:model.live="clienteId" class="w-full border-gray-100 rounded-2xl h-12 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all text-sm">
                        <option value="">Selecione o cliente...</option>
                        @foreach($clientes as $cli)
                            <option value="{{ $cli->id }}">{{ $cli->razao_social }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="bateriaId" value="Bateria em Reclamação" class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1" />
                    <select id="bateriaId" wire:model="bateriaId" class="w-full border-gray-100 rounded-2xl h-12 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all text-sm">
                        <option value="">Selecione a bateria...</option>
                        @foreach($baterias as $bat)
                            <option value="{{ $bat->id }}">{{ $bat->marca }} - {{ $bat->amperagem }}Ah</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="valeOriginalId" value="Vale de Origem (Opcional)" class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1" />
                    <select id="valeOriginalId" wire:model="valeOriginalId" class="w-full border-gray-100 rounded-2xl h-12 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all text-sm" {{ !$clienteId ? 'disabled' : '' }}>
                        <option value="">Buscar em vendas anteriores...</option>
                        @foreach($vales as $v)
                            <option value="{{ $v->id }}">Vale #{{ $v->id }} ({{ $v->created_at->format('d/m/Y') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <x-label for="laudo" value="Relato do Cliente / Sintomas" class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1" />
                    <textarea id="laudo" wire:model="laudo" rows="4" class="w-full border-gray-100 rounded-2xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all text-sm" placeholder="Descreva os sintomas alegados pelo cliente..."></textarea>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showModal', false)" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>

            <button wire:click="abrirGarantia" class="ms-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-indigo-100 transition-all flex items-center gap-2 group">
                Abrir Ordem de Serviço
            </button>
        </x-slot>
    </x-dialog-modal>
</div>
