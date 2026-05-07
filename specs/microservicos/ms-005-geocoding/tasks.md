# Tasks: MS-005 — Geocoding & Routing

**Identificador**: `MS-005-GEOCODING`
**Spec**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

---

## Phase 1: Setup e Infraestrutura

- [ ] T001: Criar repositório `ms-005-geocoding` com estrutura base de pastas
- [ ] T002: Inicializar projeto Node.js (Fastify + TypeScript + Prisma)
- [ ] T003: Configurar PostgreSQL com extensão PostGIS habilitada (`CREATE EXTENSION postgis`)
- [ ] T004: Criar schema Prisma para `EnderecoGeocodificado`, `Rota`, `Parada`, `LocalizacaoEntregador`
- [ ] T005: Criar migration SQL adicionando colunas `GEOGRAPHY(POINT)` e índices GIST para tabelas geoespaciais
- [ ] T006: Configurar `docker-compose.yml` com `ms-geocoding-api`, `postgres` (com PostGIS), `redis`
- [ ] T007: Configurar variáveis de ambiente: `GOOGLE_MAPS_API_KEY`, `REDIS_URL`, `DATABASE_URL`

---

## Phase 2: Providers de Geocodificação

- [ ] T008: Criar interface `GeocodingProvider` com contratos `geocode()` e `reverseGeocode()`
- [ ] T009: Implementar `GoogleMapsProvider` usando SDK `@googlemaps/google-maps-services-js`
- [ ] T010: Implementar `OpenStreetMapProvider` usando Nominatim API (free tier, respeitar rate limit de 1 req/s)
- [ ] T011: Implementar `GeocodingService` com: verificação de cache Redis → Google Maps → OSM fallback
- [ ] T012: Implementar normalização de endereço antes do hash de cache (lowercase, remover acentos, padronizar abreviações)
- [ ] T013: Implementar controle de cota diária Google Maps + alerta com 80% consumido

---

## Phase 3: Algoritmos de Otimização de Rota

- [ ] T014: Implementar algoritmo Nearest Neighbor (`algorithms/nearest-neighbor.ts`) — rota inicial gulosa
- [ ] T015: Implementar algoritmo 2-opt (`algorithms/two-opt.ts`) — melhoria iterativa sobre o Nearest Neighbor
- [ ] T016: Implementar `OtimizacaoService.otimizarLocal(paradas)` — usa NN + 2-opt para N ≤ 15 paradas
- [ ] T017: Implementar `OtimizacaoService.otimizarGoogle(paradas)` — usa Google Routes API para N > 15 paradas
- [ ] T018: Implementar lógica de clustering K-means para N > 50 paradas (dividir em sub-rotas por cluster geográfico)
- [ ] T019: Implementar cálculo de ETA por parada usando Google Distance Matrix API (com tráfego em tempo real)

---

## Phase 4: Serviços Core

- [ ] T020: Implementar `EtaService.recalcular(rotaId, posicaoAtual)` — recalcula ETAs das paradas restantes
- [ ] T021: Implementar `LocalizacaoService.salvar(entregadorId, lat, lng, timestamp)` com upsert eficiente
- [ ] T022: Implementar `LocalizacaoService.obterAtual(entregadorId)` para consulta da posição atual

---

## Phase 5: Consumers de Eventos

- [ ] T023: Implementar `RotaCriadaConsumer.ts` — escuta `ROTA_CRIADA`, aciona geocodificação + otimização
- [ ] T024: Implementar `LocalizacaoConsumer.ts` — escuta `LOCALIZACAO_ATUALIZADA`, aciona recálculo de ETA (com debounce de 30s por entregador)
- [ ] T025: Implementar publicadores de eventos: `ROTA_OTIMIZADA`, `ETA_ATUALIZADO`, `GEOCODIFICACAO_BAIXA_CONFIANCA`, `LIMITE_API_ATINGINDO`
- [ ] T025A: Adaptar consumers e publishers ao envelope canônico do Módulo 010 (`event_version`, `tenant_external_ref`, `correlation_id`, `causation_id`, `idempotency_key`)
- [ ] T025B: Implementar dead-letter e replay operacional compatíveis com o backbone para rotas, localização e ETA

---

## Phase 6: API Routes

- [ ] T026: Implementar `POST /api/v1/geocodificar` com validação Zod e retorno de confiança
- [ ] T027: Implementar `POST /api/v1/geocodificar/lote` com processamento paralelo controlado (máximo 10 simultâneos)
- [ ] T028: Implementar `PUT /api/v1/geocodificar/corrigir` para salvar pin manual (TTL infinito no cache)
- [ ] T029: Implementar `POST /api/v1/rotas/otimizar` — endpoint síncrono com timeout de 10s
- [ ] T030: Implementar `GET /api/v1/rotas/{id}` com paradas e ETAs
- [ ] T031: Implementar `POST /api/v1/localizacao` para receber posição do App Entregador
- [ ] T032: Implementar `GET /api/v1/localizacao/{entregador_id}` para posição atual
- [ ] T033: Implementar `DELETE /api/v1/cache/geocodificacao/{hash}` para invalidação manual de cache

---

## Phase 7: Testes

- [ ] T034: Teste unitário `NearestNeighbor` — dado 5 pontos conhecidos, verificar rota ótima esperada
- [ ] T035: Teste unitário `TwoOpt` — verificar que melhoria é igual ou melhor que a entrada
- [ ] T036: Teste unitário `GeocodingService` — verificar que cache é consultado antes da API + fallback para OSM
- [ ] T037: Teste unitário controle de cota — simular limite atingido e verificar fallback para OSM
- [ ] T038: Teste de integração `GoogleMapsProvider` em conta de desenvolvimento (sandbox com créditos gratuitos)
- [ ] T039: Teste E2E — `ROTA_CRIADA` publicado → geocodificação → otimização → `ROTA_OTIMIZADA` publicado com paradas ordenadas
- [ ] T040: Teste de deduplicação de localização — enviar 100 atualizações de posição rápidas → verificar que ETA não recalcula em cada uma (debounce)
- [ ] T041: Teste de carga — 20 paradas otimizadas concorrentemente por 5 rotas simultâneas
- [ ] T041A: Teste de contrato do envelope canônico e replay operacional contra o catálogo do Módulo 010

---

## Phase 8: Deploy e CI/CD

- [ ] T042: Criar `Dockerfile` multi-stage para API Node.js
- [ ] T043: Verificar que `docker-compose.yml` inclui inicialização do PostGIS e healthcheck do Postgres
- [ ] T044: Configurar pipeline CI/CD com testes automatizados (mockar Google Maps API nos testes)
- [ ] T045: Configurar métricas Prometheus: cache hit/miss, uso Google Maps API, latência de otimização
- [ ] T045A: Registrar endpoints síncronos no gateway do Módulo 010 com timeout, autenticação e rastreio padronizado
- [ ] T046: Criar Grafana dashboard para monitoramento de rotas (entregadores ativos, média de paradas, ETA accuracy)
- [ ] T047: Documentar limites da Google Maps API e processo de aumento de cota
- [ ] T048: Executar linting e formatação em todos os arquivos TypeScript modificados (ESLint + Prettier) antes de cada merge
