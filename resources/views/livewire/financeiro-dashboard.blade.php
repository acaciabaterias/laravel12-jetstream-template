<div class="p-6 max-w-7xl mx-auto space-y-8">
    <!-- Header Premium -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight italic">Painel Financeiro Inteligente</h1>
            <p class="text-sm text-gray-500 font-medium tracking-wide">Gestão preditiva de caixa e conciliação bancária (FR-FIN-02)</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="rodarConciliacao" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold transition-all shadow-xl shadow-indigo-100 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Rodar Conciliação
            </button>
        </div>
    </div>

    @if(session()->has('success'))
        <div class="bg-green-50 border border-green-100 text-green-700 p-4 rounded-2xl font-bold flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Cards de Fluxo de Caixa -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-indigo-600 p-8 rounded-[2rem] text-white shadow-2xl shadow-indigo-200">
            <p class="text-xs font-black uppercase tracking-widest text-indigo-200 mb-1">Saldo Projetado (7 dias)</p>
            <h2 class="text-4xl font-black tracking-tighter italic">R$ {{ number_format($resumoFluxo['saldo_projetado'] ?? 0, 2, ',', '.') }}</h2>
            <div class="mt-6 flex items-center gap-2 text-indigo-100 text-sm font-bold bg-white/10 w-fit px-3 py-1 rounded-full">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                Visão Preditiva Online
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
            <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Total a Receber</p>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight italic">R$ {{ number_format($resumoFluxo['total_receber'] ?? 0, 2, ',', '.') }}</h2>
            <p class="text-green-600 text-xs font-bold mt-2 font-mono">↗ Recebíveis Pendentes</p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
            <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Total a Pagar</p>
            <h2 class="text-3xl font-black text-red-600 tracking-tight italic">R$ {{ number_format($resumoFluxo['total_pagar'] ?? 0, 2, ',', '.') }}</h2>
            <p class="text-red-400 text-xs font-bold mt-2 font-mono">↘ Vencimentos Previstos</p>
        </div>
    </div>

    <!-- Tabela de Conciliação e Transações -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm relative overflow-hidden">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-gray-900 italic tracking-tight uppercase">Extrato da Conta</h3>
                <select wire:model.live="selectedContaId" class="text-xs font-bold border-gray-100 rounded-xl bg-gray-50 focus:ring-indigo-500">
                    @foreach($contas as $c)
                        <option value="{{ $c->id }}">{{ $c->banco }} - {{ $c->conta }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-4">
                @foreach($recentes as $t)
                    <div class="flex items-center justify-between p-4 rounded-2xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-xs
                                {{ $t->tipo === 'receita' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $t->tipo === 'receita' ? '+' : '-' }}
                            </div>
                            <div>
                                <p class="text-sm font-black text-gray-900 uppercase tracking-tight">{{ $t->categoria }}</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">{{ $t->data->format('d/m/Y') }} • {{ $t->status }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-black {{ $t->tipo === 'receita' ? 'text-green-600' : 'text-red-600' }}">
                                R$ {{ number_format($t->valor, 2, ',', '.') }}
                            </p>
                            @if($t->status === 'conciliado')
                                <span class="text-[9px] font-black italic bg-green-50 text-green-600 px-2 py-0.5 rounded-full uppercase">Check Verdinho ✓</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Alertas de Margem -->
        <div class="bg-gray-900 p-8 rounded-[2rem] text-white shadow-2xl relative overflow-hidden">
            <svg class="absolute -right-4 -bottom-4 w-48 h-48 text-white/5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
            <h3 class="text-xl font-black italic tracking-tight uppercase mb-6 text-indigo-300">Insights de Rentabilidade (BI)</h3>
            
            <div class="space-y-6">
                <!-- Mock de alertas do BI (FR-FIN-03) -->
                <div class="p-6 bg-white/5 rounded-3xl border border-white/10">
                    <p class="text-[10px] font-black uppercase text-indigo-400 tracking-widest">Alerta de Margem Baixa</p>
                    <h4 class="text-lg font-black italic mt-1">Bateria Moura 60Ah (D)</h4>
                    <p class="text-xs text-white/60 mt-2">A margem líquida caiu para **8.4%** este mês devido ao aumento do frete logístico.</p>
                </div>

                <div class="p-6 bg-green-500/10 rounded-3xl border border-green-500/20">
                    <p class="text-[10px] font-black uppercase text-green-400 tracking-widest font-mono italic">Opportunity Found</p>
                    <h4 class="text-lg font-black italic mt-1">Heliar 70Ah (E)</h4>
                    <p class="text-xs text-white/60 mt-2">Maior rentabilidade limpa detectada: **22.5%** após dedução total de comissões e impostos.</p>
                </div>
            </div>

            <button class="mt-8 w-full py-4 bg-indigo-500 hover:bg-indigo-400 rounded-2xl font-black text-sm uppercase tracking-widest transition-all">
                Ver Relatório Completo de Mark-up
            </button>
        </div>
    </div>
</div>
