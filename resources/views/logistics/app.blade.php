<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Logística BateriaExpert</title>
    <link rel="manifest" href="/manifest.json">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-900 overflow-x-hidden selection:bg-indigo-100">

    <div x-data="deliveryApp()" x-init="initApp()" class="min-h-screen flex flex-col max-w-md mx-auto bg-white shadow-2xl relative">
        
        <!-- Header Móvel -->
        <header class="bg-indigo-700 text-white p-5 sticky top-0 z-50 shadow-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-black tracking-tighter uppercase italic">BateriaExpert</h1>
                    <p class="text-[10px] font-bold text-indigo-200 uppercase tracking-widest leading-none mt-0.5">App do Entregador</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-bold text-indigo-300 uppercase tracking-tighter" x-text="online ? 'Conectado' : 'Offline'"></span>
                        <div class="w-2 h-2 rounded-full shadow-sm" :class="online ? 'bg-green-400 animate-pulse' : 'bg-red-400'"></div>
                    </div>
                    <div class="w-10 h-10 rounded-full border-2 border-indigo-500 overflow-hidden bg-white/10 flex items-center justify-center font-bold text-sm">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Conteúdo Principal -->
        <main class="flex-1 p-4 space-y-4 pb-32">
            
            @if(!$rota)
                <div class="flex flex-col items-center justify-center py-20 text-center space-y-4 opacity-75">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center text-gray-300">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Nenhuma rota ativa</h3>
                    <p class="text-sm text-gray-500 px-10">Você não possui rotas de entrega atribuídas para hoje.</p>
                </div>
            @else
                <!-- Cards de Paradas -->
                <template x-for="(ponto, index) in pontos" :key="ponto.id">
                    <div class="relative bg-white rounded-3xl border transition-all duration-300 active:scale-95"
                         :class="ponto.status === 'concluido' ? 'border-green-100 bg-green-50/10' : 'border-gray-100 shadow-sm shadow-gray-100/50'">
                        
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs"
                                         :class="ponto.status === 'concluido' ? 'bg-green-500 text-white' : 'bg-gray-900 text-white'"
                                         x-text="index + 1"></div>
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest" x-text="'PARADA #' + ponto.id"></p>
                                        <h3 class="font-black text-gray-900 leading-tight uppercase" x-text="ponto.cliente"></h3>
                                    </div>
                                </div>
                                <span class="text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-tighter"
                                      :class="statusClasses(ponto.status)"
                                      x-text="ponto.status"></span>
                            </div>

                            <div class="space-y-3 mb-6">
                                <template x-for="item in ponto.itens" :key="item.id">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600 truncate mr-4"><b x-text="item.quantidade + 'x'"></b> <span x-text="item.bateria"></span></span>
                                        <span class="font-mono font-bold text-gray-900" x-text="formatMoney(item.preco)"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Ações da Parada -->
                            <div class="flex gap-2">
                                <template x-if="ponto.status !== 'concluido'">
                                    <button @click="abrirParada(ponto)" class="flex-1 bg-gray-900 text-white font-black py-4 rounded-2xl shadow-xl shadow-gray-900/20 active:bg-black transition-all text-xs tracking-widest uppercase italic">
                                        Check-in paragem
                                    </button>
                                </template>
                                <template x-if="ponto.status === 'concluido'">
                                    <button class="flex-1 border-2 border-green-500 text-green-600 font-black py-4 rounded-2xl text-xs tracking-widest uppercase italic" disabled>
                                        ✓ Entregue
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            @endif
        </main>

        <!-- Modal de Operação no Ponto -->
        <div x-show="modalAberto" x-cloak 
             class="fixed inset-0 z-[100] flex items-end justify-center px-4 pb-4 bg-gray-900/80 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white w-full max-w-md rounded-t-[40px] shadow-2xl overflow-hidden animate-slide-up p-8"
                 @click.away="modalAberto = false">
                
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-2xl font-black text-gray-900 tracking-tighter uppercase italic" x-text="pontoAtivo?.cliente"></h2>
                        <p class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Finalização de Entrega</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Peso da Sucata -->
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Peso Real da Sucata (KG)</label>
                        <div class="flex items-center gap-4">
                            <input type="number" x-model="pesoColetado" class="flex-1 bg-gray-100 border-none rounded-2xl h-14 text-center font-black text-xl text-gray-900 focus:ring-2 focus:ring-indigo-600 transition-all">
                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black italic">KG</div>
                        </div>
                    </div>

                    <!-- Pagamento -->
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Recebimento (R$)</label>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <button @click="metodo = 'pix'" :class="metodo === 'pix' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-50 text-gray-500 border-gray-100'" class="h-12 rounded-xl border-2 text-xs font-bold transition-all">PIX</button>
                            <button @click="metodo = 'dinheiro'" :class="metodo === 'dinheiro' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-50 text-gray-500 border-gray-100'" class="h-12 rounded-xl border-2 text-xs font-bold transition-all">Dinheiro</button>
                        </div>
                        <input type="number" x-model="valorPago" class="w-full bg-gray-50 border-none rounded-2xl h-14 text-center font-black text-xl text-indigo-600 focus:ring-2 focus:ring-indigo-600 transition-all" placeholder="Valor recebido...">
                    </div>

                    <button @click="finalizarPonto()" class="w-full bg-indigo-600 text-white font-black py-5 rounded-3xl shadow-2xl shadow-indigo-200 active:scale-95 transition-all uppercase tracking-widest italic mt-4">
                        Confirmar Entrega
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer / GPS Control -->
        <footer class="bg-white border-t border-gray-100 p-5 sticky bottom-0 z-50 safe-bottom">
            <div class="flex items-center justify-between">
                <button @click="sincronizar()" class="flex items-center gap-2 bg-gray-900 text-white px-4 py-3 rounded-2xl active:scale-95 transition-all shadow-lg" :disabled="syncing">
                    <svg class="w-5 h-5" :class="syncing ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span class="text-xs font-black uppercase tracking-tighter">Sincronizar</span>
                </button>
            </div>
        </footer>

    </div>

    <!-- Script de Logística & Sync Offline -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js');
            });
        }

        function deliveryApp() {
            return {
                online: navigator.onLine,
                syncing: false,
                modalAberto: false,
                pontoAtivo: null,
                pesoColetado: 0,
                metodo: 'pix',
                valorPago: 0,
                
                // Dados iniciais (Vindo do Blade)
                pontos: @js($rota ? $rota->pontos->map(fn($p) => [
                    'id' => $p->id,
                    'cliente' => $p->vale->cliente->nome_fantasia ?: $p->vale->cliente->razao_social,
                    'status' => $p->status,
                    'itens' => $p->vale->itens->map(fn($i) => [
                        'id' => $i->id,
                        'quantidade' => $i->quantidade,
                        'bateria' => $i->bateria->marca,
                        'preco' => $i->preco_unitario_final
                    ])
                ]) : []),

                initApp() {
                    window.addEventListener('online', () => this.online = true);
                    window.addEventListener('offline', () => this.online = false);

                    // Inicializar Loop de GPS (Broadcasting)
                    this.startGpsLoop();
                },

                abrirParada(ponto) {
                    this.pontoAtivo = ponto;
                    this.modalAberto = true;
                    this.pesoColetado = 0;
                    this.valorPago = 0;
                },

                finalizarPonto() {
                    const idx = this.pontos.findIndex(p => p.id === this.pontoAtivo.id);
                    this.pontos[idx].status = 'concluido';
                    this.pontos[idx].peso_coletado = this.pesoColetado;
                    this.pontos[idx].recebimentos = [{ valor: this.valorPago, metodo: this.metodo }];
                    
                    this.modalAberto = false;
                    
                    // Salvar no local storage (para simular IndexedDB para esta entrega rápida)
                    localStorage.setItem('pending_sync', JSON.stringify(this.pontos.filter(p => p.status === 'concluido')));
                },

                async sincronizar() {
                    this.syncing = true;
                    const pending = JSON.parse(localStorage.getItem('pending_sync') || '[]');
                    
                    if (pending.length === 0) {
                        alert('Nada a sincronizar.');
                        this.syncing = false;
                        return;
                    }

                    try {
                        const response = await fetch('/api/v1/logistics/sync', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                updates: pending.map(p => ({
                                    ponto_entrega_id: p.id,
                                    status: 'concluido',
                                    peso_sucata_coletado: p.peso_coletado,
                                    recebimentos: p.recebimentos
                                }))
                            })
                        });

                        if (response.ok) {
                            localStorage.removeItem('pending_sync');
                            alert('Sincronização concluída!');
                        }
                    } catch (e) {
                        alert('Ainda offline. Tente novamente quando houver sinal.');
                    } finally {
                        this.syncing = false;
                    }
                },

                startGpsLoop() {
                    setInterval(() => {
                        if (!this.online) return;

                        navigator.geolocation.getCurrentPosition((pos) => {
                            // Enviar via WebSocket (T013)
                            // Aqui o ideal seria um POST para disparar o Evento no Canal Reverb
                            // Vamos simular o disparo direto se houvesse uma rota de broadcast
                            console.log('Publishing GPS:', pos.coords.latitude, pos.coords.longitude);
                        });
                    }, 10000);
                },

                formatMoney(v) {
                    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v);
                },

                statusClasses(s) {
                    const maps = {
                        'pendente': 'bg-gray-100 text-gray-500',
                        'concluido': 'bg-green-100 text-green-700',
                        'em_transito': 'bg-blue-100 text-blue-700'
                    };
                    return maps[s] || 'bg-gray-100 text-gray-500';
                }
            }
        }
    </script>
</body>
</html>
