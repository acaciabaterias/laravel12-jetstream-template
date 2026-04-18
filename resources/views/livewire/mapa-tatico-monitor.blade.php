<div class="px-4 md:px-6 py-8 max-w-7xl mx-auto">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <!-- Header / Stats -->
        <div class="p-6 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50/50">
            <div>
                <h2 class="text-2xl font-black text-gray-800 tracking-tight">Mapa Tático de Frota</h2>
                <p class="text-sm text-gray-500 font-medium mt-1">Monitoramento em tempo real (WebSockets) via Filial #{{ $filialId }}</p>
            </div>
            
            <div class="flex items-center gap-6 overflow-x-auto pb-2 md:pb-0">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-gray-600 uppercase tracking-widest">Online: <span id="online-count">0</span></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span class="text-xs font-bold text-gray-600 uppercase tracking-widest">Motoristas: {{ count($activeEntregadores) }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row min-h-[600px]">
            <!-- Sidebar: Lista de Entregadores -->
            <div class="w-full lg:w-80 border-r border-gray-100 bg-white overflow-y-auto max-h-[300px] lg:max-h-none">
                <div class="p-4 space-y-3" id="drivers-list">
                    @forelse($activeEntregadores as $ent)
                        <div id="driver-card-{{ $ent['id'] }}" class="p-4 rounded-xl border border-gray-100 hover:border-indigo-200 transition-all group relative">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-700 font-bold">
                                    {{ substr($ent['nome'], 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-indigo-700 transition-colors">{{ $ent['nome'] }}</h4>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span id="status-dot-{{ $ent['id'] }}" class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                        <span id="status-text-{{ $ent['id'] }}" class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Offline</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 text-[10px] text-gray-400 font-mono hidden" id="coords-{{ $ent['id'] }}">
                                LAT: <span class="lat">0</span> | LNG: <span class="lng">0</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 opacity-50">
                            <p class="text-xs text-gray-400">Nenhuma rota ativa para hoje.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Mapa Container -->
            <div class="flex-1 relative bg-gray-100">
                <div id="map" class="absolute inset-0 z-0"></div>
                
                <!-- Overlay de carregamento -->
                <div id="map-loader" class="absolute inset-0 z-10 bg-white/80 backdrop-blur-sm flex items-center justify-center transition-opacity duration-500">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-xs font-bold text-indigo-800 uppercase tracking-widest">Carregando Mapa...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Styles for Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('livewire:navigated', () => {
            initTacticalMap();
        });

        // Fallback para carregamento inicial se não for Livewire Navigated
        window.addEventListener('load', () => {
            if (typeof L !== 'undefined' && !window.mapInitialized) {
                initTacticalMap();
            }
        });

        function initTacticalMap() {
            if (window.mapInitialized) return;
            
            const mapElement = document.getElementById('map');
            if (!mapElement) return;

            // Coordenadas padrão (podem vir de config da filial depois)
            const map = L.map('map').setView([-23.5505, -46.6333], 13);
            window.mapInstance = map;

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Hide loader
            document.getElementById('map-loader').style.opacity = '0';
            setTimeout(() => document.getElementById('map-loader').style.display = 'none', 500);

            window.markers = {};
            window.mapInitialized = true;

            // Iniciar Escuta WebSocket (Echo)
            const filialId = @js($filialId);
            const channelName = `filial.${filialId}.logistica`;

            window.Echo.join(channelName)
                .here((users) => {
                    document.getElementById('online-count').innerText = users.length;
                    users.forEach(user => updateDriverStatus(user.id, true));
                })
                .joining((user) => {
                    const count = parseInt(document.getElementById('online-count').innerText);
                    document.getElementById('online-count').innerText = count + 1;
                    updateDriverStatus(user.id, true);
                })
                .leaving((user) => {
                    const count = parseInt(document.getElementById('online-count').innerText);
                    document.getElementById('online-count').innerText = Math.max(0, count - 1);
                    updateDriverStatus(user.id, false);
                })
                .listen('.gps.atualizado', (e) => {
                    console.log('GPS Update:', e);
                    updateMarker(e.entregador_id, e.nome, e.lat, e.lng);
                });
        }

        function updateDriverStatus(id, online) {
            const dot = document.getElementById(`status-dot-${id}`);
            const text = document.getElementById(`status-text-${id}`);
            const card = document.getElementById(`driver-card-${id}`);

            if (dot && text && card) {
                if (online) {
                    dot.classList.remove('bg-gray-300');
                    dot.classList.add('bg-green-500', 'animate-pulse');
                    text.innerText = 'Online';
                    text.classList.remove('text-gray-400');
                    text.classList.add('text-green-600');
                    card.classList.add('bg-indigo-50/30', 'border-indigo-100');
                } else {
                    dot.classList.add('bg-gray-300');
                    dot.classList.remove('bg-green-500', 'animate-pulse');
                    text.innerText = 'Offline';
                    text.classList.add('text-gray-400');
                    text.classList.remove('text-green-600');
                    card.classList.remove('bg-indigo-50/30', 'border-indigo-100');
                }
            }
        }

        function updateMarker(id, nome, lat, lng) {
            const coordsDiv = document.getElementById(`coords-${id}`);
            if (coordsDiv) {
                coordsDiv.classList.remove('hidden');
                coordsDiv.querySelector('.lat').innerText = lat.toFixed(4);
                coordsDiv.querySelector('.lng').innerText = lng.toFixed(4);
            }

            if (window.markers[id]) {
                window.markers[id].setLatLng([lat, lng]);
            } else {
                const icon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div class="w-8 h-8 bg-indigo-600 border-2 border-white rounded-full shadow-lg flex items-center justify-center text-white font-bold text-[10px] transform -translate-x-1/2 -translate-y-1/2 scale-110 active:scale-125 transition-transform animate-bounce-short">${nome.substr(0, 1)}</div>`,
                    iconSize: [30, 42],
                    iconAnchor: [15, 42]
                });

                window.markers[id] = L.marker([lat, lng], {icon: icon}).addTo(window.mapInstance);
                window.markers[id].bindPopup(`<b class="text-indigo-800">${nome}</b><br><span class="text-[10px] text-gray-500">Última atualização agora.</span>`);
            }
        }
    </script>

    <style>
        @keyframes bounce-short {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }
        .animate-bounce-short {
            animation: bounce-short 1s infinite;
        }
        #map { height: 100%; width: 100%; border-radius: 0 0 1rem 0; }
        @media (max-width: 1024px) {
            #map { border-radius: 0 0 1rem 1rem; }
        }
        .leaflet-container { font-family: inherit; }
    </style>
</div>
