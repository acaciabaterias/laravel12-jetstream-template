# Microserviço Specification: MS-005 — Geocoding & Routing

**Identificador**: `MS-005-GEOCODING`
**Status**: Ready for Implementation
**Tipo**: Microserviço Autônomo (projeto separado do ERP)
**Dependências ERP**: Módulo 006 (Logística e Entregas)

---

## Overview

O MS-005 é o microserviço responsável pela **inteligência geográfica** do ERP de baterias, provendo geocodificação de endereços, cálculo de ETA (tempo estimado de chegada) e **otimização de rotas de entrega** (Traveling Salesman Problem — TSP). Ele alimenta o Módulo 006 e o App do Entregador com dados geográficos precisos e rotas otimizadas.

**Problema que resolve:**
Uma loja de baterias que faz entregas precisa organizar a ordem das paradas do entregador para minimizar o tempo de deslocamento e combustível. O MS-005 recebe a lista de entregas do dia e devolve a sequência ótima das paradas com ETAs calculados em tempo real.

**Estratégia de providers:**
- **Primário**: Google Maps API (Geocoding API + Directions API + Distance Matrix API)
- **Fallback**: OpenStreetMap / Nominatim (gratuito, sem limite de requisições)
- **Cache**: Geocodificação de endereços cacheada por 30 dias (TTL) para evitar rechamadas desnecessárias

---

## Key Entities

- **EnderecoGeocodificado**: (id, endereco_hash, logradouro, numero, bairro, cidade, uf, cep, latitude, longitude, provider_usado, confianca, created_at, expires_at)
- **Rota**: (id, tenant_id_externo, base_operacional_id, data_entrega, status [pendente/em_andamento/concluida], paradas_json, distancia_total_km, duracao_estimada_min, otimizada_em)
- **Parada**: (id, rota_id, ordem, entrega_id, cliente_nome, endereco, latitude, longitude, eta_chegada, status [pendente/chegou/entregue/falhou], chegada_real, saída_real)
- **LocalizacaoEntregador**: (id, entregador_id, latitude, longitude, velocidade_kmh, heading, timestamp)

---

## Functional Requirements

### FR-005-01: Geocodificação de Endereços
- O MS DEVE converter endereços textuais em coordenadas (Latitude, Longitude)
- O MS DEVE utilizar cache com TTL 30 dias para endereços já geocodificados (hash do endereço normalizado como chave)
- Se não encontrado no cache, chamar Google Maps Geocoding API (primário)
- Se Google Maps falhar ou retornar confiança < 0.7, tentar OpenStreetMap Nominatim como fallback
- O MS DEVE retornar o nível de confiança da geocodificação (high/medium/low)
- Endereços com confiança `low` devem sinalizar para ajuste manual via pin no App do Entregador

### FR-005-02: Otimização de Rota (TSP)
- Dado uma lista de endereços de entrega (N paradas) e um ponto de partida (`base_operacional` do tenant), o MS DEVE calcular a ordem ótima das paradas
- Para N ≤ 15 paradas: algoritmo local (nearest neighbor + 2-opt improvement) — execução em memória, sem chamadas externas
- Para N > 15 paradas: algoritmo heurístico via Google Maps Routes API com waypoints optimization (ou OR-Tools como fallback)
- Para N > 50 paradas: clustering K-means geográfico antes da otimização, gerando múltiplas sub-rotas
- O MS DEVE retornar a lista de paradas na ordem otimizada com ETAs estimados para cada parada
- O ETA DEVE considerar tráfego em tempo real (quando disponível pelo provider)

### FR-005-03: Cálculo de ETA Dinâmico
- O MS DEVE recalcular ETAs em tempo real conforme o entregador avança na rota
- Quando receber evento `LOCALIZACAO_ATUALIZADA`, recalcular ETAs das paradas restantes
- Publicar `ETA_ATUALIZADO` com novos tempos estimados para cada parada restante

### FR-005-04: Rastreamento de Localização
- O MS DEVE receber e persistir localizações do App do Entregador (latitude, longitude, timestamp)
- Localização salva com retention de 90 dias para auditoria de rotas
- Endpoint de consulta de localização atual do entregador para o ERP

### FR-005-05: Ajuste Manual de Coordenadas
- O MS DEVE aceitar correção manual de coordenadas (pin no mapa) quando geocodificação retorna confiança `low`
- Coordenadas manuais DEVEM sobrescrever o cache e ser marcadas como `ajustado_manualmente: true`

### FR-005-06: Gerenciamento de Cache de Geocodificação
- Cache armazenado em Redis (TTL 30 dias)
- Cache de coordenadas manuais (pin no mapa) NUNCA expira (TTL infinito)
- Endpoint de invalidação de cache por endereço específico (para correções)
- Métricas de hit/miss do cache para monitoramento

---

## User Stories

### US-005-01: Otimização de Rota Diária
**Como** gerente de logística (Módulo 006),
**Quando** confirmo a lista de entregas do dia,
**Quero** que o sistema organize automaticamente a ordem ótima das paradas,
**Para que** o entregador percorra a menor distância possível e economize tempo e combustível.

**Critérios de Aceite:**
- Rota otimizada calculada em < 5 segundos para até 20 paradas
- Ordem das paradas e ETA de cada uma retornados
- Evento `ROTA_OTIMIZADA` publicado no broker
- App do Entregador recebe a rota atualizada automaticamente

### US-005-02: ETA em Tempo Real no App
**Como** entregador usando o App,
**Enquanto** realizo as entregas,
**Quero** ver o ETA atualizado para cada parada conforme me movo,
**Para que** eu possa informar clientes com precisão sobre o horário de chegada.

**Critérios de Aceite:**
- ETA recalculado sempre que o entregador avança > 200m ou a cada 5 minutos
- Atualização visível no App em < 3 segundos após recálculo
- Eventos de localização processados sem afetar a performance do App

### US-005-03: Ajuste Manual de Endereço Não Localizado
**Como** entregador usando o App,
**Quando** o GPS não consegue localizar corretamente o endereço de um cliente,
**Quero** poder marcar a localização manualmente no mapa,
**Para que** futuras entregas no mesmo endereço não tenham o mesmo problema.

**Critérios de Aceite:**
- Endereços com confiança `low` marcados visualmente no App do Entregador
- Entregador pode arrastar o pin para a localização correta
- Coordenadas salvas permanentemente no cache (sem expiração)
- Endereço corrigido usado automaticamente em futuras rotas

### US-005-04: Fallback Offline de ETA
**Como** entregador em área sem internet,
**Quando** o App perde conexão,
**Quero** que os ETAs estimados ainda estejam disponíveis offline,
**Para que** eu possa continuar as entregas sem depender de conexão constante.

**Critérios de Aceite:**
- Rota e ETAs cacheados localmente no App do Entregador ao início do dia
- Sistema funciona offline com dados do cache por até 8 horas
- Sincronização automática quando conexão for restaurada

---

## Eventos

### Eventos que o MS-005 **ESCUTA**:

| Evento | Publicado por | Ação |
|---|---|---|
| `ROTA_CRIADA` | Módulo 006 | Aciona geocodificação + otimização de rota |
| `LOCALIZACAO_ATUALIZADA` | App Entregador | Persiste localização e recalcula ETAs |
| `PARADA_STATUS_ATUALIZADO` | App Entregador | Atualiza status da parada (chegou/entregue/falhou) |

### Eventos que o MS-005 **PUBLICA**:

| Evento | Consumido por | Descrição |
|---|---|---|
| `ROTA_OTIMIZADA` | Módulo 006, App Entregador | Rota calculada com ordem e ETAs |
| `ETA_ATUALIZADO` | App Entregador, Módulo 006 | Novos ETAs para paradas restantes |
| `GEOCODIFICACAO_BAIXA_CONFIANCA` | App Entregador | Endereço precisa de ajuste manual |
| `LIMITE_API_ATINGINDO` | Sistema de Alertas | ~80% do limite mensal da Google Maps API consumido |

---

## API Endpoints (REST Interno)

| Método | Endpoint | Descrição |
|---|---|---|
| `POST` | `/api/v1/geocodificar` | Geocodifica um endereço |
| `POST` | `/api/v1/geocodificar/lote` | Geocodifica lista de endereços |
| `PUT` | `/api/v1/geocodificar/corrigir` | Salva coordenadas ajustadas manualmente |
| `POST` | `/api/v1/rotas/otimizar` | Otimiza rota dado lista de paradas |
| `GET` | `/api/v1/rotas/{id}` | Consulta rota otimizada |
| `POST` | `/api/v1/localizacao` | Recebe localização do App Entregador |
| `GET` | `/api/v1/localizacao/{entregador_id}` | Localização atual do entregador |
| `POST` | `/api/v1/eta/recalcular` | Recalcula ETAs da rota dado posição atual |
| `DELETE` | `/api/v1/cache/geocodificacao/{hash}` | Invalida cache de um endereço |
| `GET` | `/api/v1/health` | Health check |

---

## Edge Cases

- **Endereço não localizado** (confiança baixa ou nenhum resultado): Retornar `GEOCODIFICACAO_BAIXA_CONFIANCA` + permitir ajuste manual por pin no App. Nunca bloquear a criação da rota por endereço não encontrado — usar coordenadas aproximadas e marcar para ajuste.
- **Trânsito em tempo real indisponível** (fora de cobertura Google ou limite de API): Usar tempo de viagem estimado por distância média (30 km/h para área urbana, 60 km/h para rodovias) como fallback.
- **Limite de requisições Google Maps API atingido**: Fallback imediato para OpenStreetMap Nominatim + Routing Machine (OSRM). Publicar `LIMITE_API_ATINGINDO` quando consumo atingir 80% do limite mensal.
- **N paradas muito grandes (> 50 entregas)**: Dividir em clusters geográficos (K-means) antes de otimizar cada cluster independentemente. Retornar múltiplas sub-rotas sugeridas.
- **Entregador perde conectividade**: App deve manter rota cacheada localmente. Ao reconectar, sincronizar status das paradas e atualizar ETAs restantes.
- **Dois entregadores para a mesma área**: O MS deve gerenciar rotas independentes por `entregador_id` sem interferência.

---

## Success Criteria

- **SC-005-01**: Rota otimizada calculada em < 5 segundos para até 20 paradas
- **SC-005-02**: Taxa de cache hit de geocodificação ≥ 70% após 30 dias de operação (redução de custo Google Maps)
- **SC-005-03**: ETA recalculado e publicado em < 3 segundos após receber `LOCALIZACAO_ATUALIZADA`
- **SC-005-04**: 100% das falhas da Google Maps API resultam em fallback para OpenStreetMap sem erro para o usuário
- **SC-005-05**: Zero bloqueios de entrega por endereço não encontrado (sempre há fallback ou ajuste manual)
- **SC-005-06**: Payload de rota retornado pelo endpoint `POST /api/v1/rotas/otimizar` é auto-contido (coordenadas, ETAs, ordem e metadados de parada incluídos), sem necessidade de lookups adicionais, permitindo que o App armazene e sirva os dados offline sem dependência de chamadas subsequentes ao MS-005
