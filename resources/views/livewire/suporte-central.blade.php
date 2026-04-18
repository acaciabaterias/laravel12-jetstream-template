<div class="p-6 max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight italic">Central de Suporte</h1>
            <p class="text-sm text-gray-500 font-medium tracking-wide">Busca por clientes, nº de série ou histórico de vendas</p>
        </div>
        
        <form wire:submit="search" class="w-full md:w-auto relative flex gap-2">
            <div class="relative flex-1 min-w-[300px]">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input wire:model="search" type="text" class="block w-full pl-12 pr-4 py-4 border-gray-200 bg-gray-50/50 rounded-2xl shadow-inner focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all text-sm font-medium" placeholder="Nome, CPF ou Nº de Série...">
            </div>
            <button type="submit" class="bg-gray-900 hover:bg-black text-white px-8 rounded-2xl font-bold transition-all shadow-xl shadow-gray-200">
                Buscar
            </button>
        </form>
    </div>

    @if($results === 'empty')
        <div class="bg-red-50 border border-red-100 p-8 rounded-[2rem] text-center">
            <p class="text-red-800 font-bold tracking-tight italic">Nenhum registro localizado para "{{ $search }}".</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Detalhes do Cliente/Contexto -->
        <div class="lg:col-span-1 space-y-6">
            @if(is_array($results) && isset($results['cliente']))
                <div class="bg-indigo-600 p-8 rounded-[2rem] text-white shadow-2xl shadow-indigo-200 relative overflow-hidden">
                    <svg class="absolute -right-4 -bottom-4 w-32 h-32 text-indigo-500/20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                    <h3 class="text-xs font-black uppercase tracking-widest text-indigo-200 mb-2">Cliente Identificado</h3>
                    <h2 class="text-2xl font-black italic tracking-tight">{{ $results['cliente']->razao_social }}</h2>
                    <p class="text-indigo-100 text-sm mt-1 opacity-80">{{ $results['cliente']->cnpj }}</p>
                    
                    <div class="mt-8 grid grid-cols-2 gap-4">
                        <div class="bg-white/10 p-4 rounded-2xl backdrop-blur-sm">
                            <p class="text-[10px] font-black uppercase text-indigo-200">Saldo Sucata</p>
                            <p class="text-xl font-black">{{ number_format($results['cliente']->saldo_sucata_kg, 1) }} kg</p>
                        </div>
                        <div class="bg-white/10 p-4 rounded-2xl backdrop-blur-sm">
                            <p class="text-[10px] font-black uppercase text-indigo-200">Telefone</p>
                            <p class="text-sm font-bold">{{ $results['cliente']->telefone }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(is_array($results) && isset($results['venda']))
                <div class="bg-white p-8 rounded-[2rem] border border-green-100 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-widest text-green-600 mb-4 flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        Produto Localizado (SN)
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-400 font-bold uppercase">Marca</span>
                            <span class="text-sm font-black text-gray-900 tracking-tight">{{ $results['produto']->marca }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-400 font-bold uppercase">Capacidade</span>
                            <span class="text-sm font-black text-gray-900 tracking-tight">{{ $results['produto']->amperagem }}Ah</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-400 font-bold uppercase">Vendido em</span>
                            <span class="text-sm font-black text-gray-900 tracking-tight">{{ $results['venda']->vale->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Timeline Histórico -->
        <div class="lg:col-span-2">
            @if($clientHistory)
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm min-h-[500px]">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight italic mb-8 uppercase">Histórico do Cliente</h3>
                    
                    <div class="relative">
                        <!-- Linha Vertical -->
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-100"></div>

                        <div class="space-y-8 relative">
                            @foreach($clientHistory as $item)
                                <div class="flex gap-8 group">
                                    <div class="relative">
                                        <div class="w-8 h-8 rounded-full border-4 border-white shadow-sm flex items-center justify-center z-10 relative
                                            {{ $item['type'] === 'venda' ? 'bg-green-500' : 'bg-indigo-600' }}">
                                            @if($item['type'] === 'venda')
                                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                            @else
                                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-1 pb-8 border-b border-gray-50 last:border-0">
                                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ $item['date']->format('d/m/Y H:i') }}</span>
                                        <h4 class="text-md font-black text-gray-900 mt-1 italic tracking-tight uppercase">{{ $item['title'] }}</h4>
                                        <p class="text-sm text-gray-500 font-medium mt-1">{{ $item['description'] }}</p>
                                        
                                        <div class="mt-4 flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-tighter
                                                {{ $item['status'] === 'faturado' || $item['status'] === 'concluida' ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-600' }}">
                                                {{ str_replace('_', ' ', $item['status']) }}
                                            </span>
                                            <button class="text-[10px] font-black text-indigo-600 uppercase hover:underline">Ver Detalhes</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 p-20 rounded-[2rem] flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <p class="text-gray-400 font-bold italic tracking-tight underline decoration-indigo-200">Utilize a busca para visualizar o histórico de um cliente ou produto.</p>
                </div>
            @endif
        </div>
    </div>
</div>
