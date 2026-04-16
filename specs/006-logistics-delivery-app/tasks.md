# Tasks: Módulo de Logística e App do Entregador

**Feature Branch**: `006-logistics-delivery-app`
**Spec File**: [spec.md](spec.md)

## Phase 1: Database Setup & Models
- [ ] T001: Criar migration para `rotas_entrega` (id, entregador_id, filial_id, data_rota, status, veiculo_id).
- [ ] T002: Criar migration pivotada relacional para `pontos_entrega` (rota_id, vale_id, ordem_parada, status, peso_sucata_coletado).
- [ ] T003: Criar migration isolada para `recebimentos_moveis` registrando parciais de pagamento atrelados ao local móvel com status de sync.
- [ ] T004: Instanciar Models (`RotaEntrega`, `PontoEntrega`, `RecebimentoMovel`) adicionando as restrições de Global Scopes `Tenant`.

## Phase 2: Livewire Web Dashboard (Tático / Matriz)
- [ ] T005: Componente Livewire "Montagem de Rotas", permitindo que o gestor filtre e puxe de uma fila drag and drop os Vales emitidos ativando eles na caçamba de um entregador.
- [ ] T006: Habilitar o servidor de mensageria WebSockets nativo local implementando `laravel/reverb`.
- [ ] T007: Construir Painel Interativo Geográfico com biblioteca robusta de JavaScript integrando assinaturas via Livewire JS listeners das correntes coordenadas ativas provindas da rota dos motoristas que estão conectados.

## Phase 3: PWA Entregador Offline (Tecnologia Mobile-First)
- [ ] T008: Configurar framework PWA instalável injetando Manifest.json, ícones de desktop mobile e serviceWorker isolado no `app.blade.php`.
- [ ] T009: Codificar UI do Celular em Tailwind + Alpine.js que priorize clareza sobre Vales em aberto, navegação entre Pontos de Parada da rota ativa em blocos grandes adaptados a toque contínuo.
- [ ] T010: Engine de persistência JavaScript consumindo/captando inputs do Celular na prateleira Local do Navegador `window.indexedDB` permitindo manipulação da sucata real x sucata imaginada em Offline.

## Phase 4: Sincronismo Core Local e Remoto 
- [ ] T011: Elaborar algoritmos lógicos e UI/UX validando Pagamentos Mistos e Recálculo Matemático Instantâneo do Net Price no front-end do dispositivo e forçar gravação em `recebimentos_moveis` locale (Off).
- [ ] T012: Capturar Evento HTML5 `window.addEventListener('online')` acionando a trigger de disparo e desaguamento ordenado do cache via POST de todos os requests pausados até a API final para conversão de Vales e Quitação Financeira de sucata efetivamente recolhida.
- [ ] T013: API Web Geolocation no Alpine.JS despachando a cada decêndio de segundo eixos geográficos pelo fluxo do Canal WebSocket instanciado do Entregador da Filial ativa.

## Phase 5: Amarrações a Módulo 004 e 005
- [ ] T014: Criar Observers que interagem em escuta impedindo a finalização manual do ciclo na Matriz (Passo final do FR-LOG-05) caso a integridade do app acuse valores conflitantes ou sem batimentos no fechamento global da Rota.
- [ ] T015: Trigger validando e efetuando a baixa exata e somatória na Conta de Crédito Corrente da Logística Reversa de um dado cliente à partir da sucata confirmada sincronizada após viagem finalizada pelo Entregador na doca.

## Phase 6: E2E and Edge Case Certifications
- [ ] T016: Execução em bateria de Teste (Motor Unitário Dusk via Chrome Headless) isolando derrubada de Rede do browser e atestando os saves do banco local indexado.
- [ ] T017: Garantir que Scopes de rastreamento blindam que a "Unidade Filial X" capte escutas geográficas ou espionagem logísticas oriundas de transportes locados da "Unidade Y filial".
