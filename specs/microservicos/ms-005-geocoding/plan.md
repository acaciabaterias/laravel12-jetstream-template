# Implementation Plan: MS-005 — Geocoding & Routing

**Identificador**: `MS-005-GEOCODING`
**Spec**: [spec.md](spec.md)
**Repositório**: `ms-005-geocoding` (projeto separado do ERP)

---

## Constitution Check

> Requisito da constitution v1.5.0 — Quality Gate 1 e 2: *"Every implementation plan MUST include a Constitution Check. Constitution check gates in planning MUST pass before implementation begins."*

| Functional Requirement | Princípio da Constitution | Status | Notas |
|---|---|---|---|
| FR-005-01: Geocodificação de Endereços | **II. Mobile-First Field Operations** — "enabling real-time access to routes" | ✅ Alinhado | Geocodificações precisas são pré-requisito para rotas funcionais no App do Entregador |
| FR-005-02: Otimização de Rota (TSP) | **II. Mobile-First Field Operations** — "real-time access to routes, on-site adjustments" + **I. Business Domain Specialization** — otimização de logística de revenda de baterias | ✅ Alinhado | Rotas otimizadas reduzem custo operacional específico do domínio |
| FR-005-03: Cálculo de ETA Dinâmico | **II. Mobile-First Field Operations** — "real-time access" e "seamless integration between field and in-store operations" | ✅ Alinhado | ETAs em tempo real conectam campo (entregador) à loja (gestor) |
| FR-005-04: Rastreamento de Localização | **II. Mobile-First Field Operations** — "direct recording of payment receipts" e rastreamento de campo | ✅ Alinhado | |
| FR-005-05: Ajuste Manual de Coordenadas | **II. Mobile-First Field Operations** — "on-site adjustments" pelo entregador | ✅ Alinhado | Entregador corrige pin diretamente no App (mobile-first) |
| FR-005-06: Gerenciamento de Cache | **I. Business Domain Specialization** — otimização operacional + redução de custos de API | ✅ Alinhado | Cache de 30 dias reduz custo de Google Maps para operação de revenda |

**Princípios sem conflito identificado:** III, IV, V, VI — não impactados diretamente por este MS.

**Multi-Tenancy (Architecture Principle):**
> A entidade `Rota` contém `filial_id` (visível em spec Key Entities), garantindo isolamento por filial conforme exigido. Cada filial gerencia suas próprias rotas de entrega independentemente. ✅ Alinhado.

**Stack Tecnológica (Quality Gate — Technology Stack Constraints):**
- Node.js 20+ (Fastify): ✅ Serviço autônomo. Justificativa: SDK oficial Google Maps em Node.js, alta performance para processamento de coordenadas em memória
- PostgreSQL 15+ com PostGIS: ✅ PostgreSQL é stack canônico; extensão PostGIS é adição específica e necessária para queries geoespaciais (GIST index, ST_DWithin)
- Redis: ✅ Stack canônico

---

## Stack Tecnológica

| Camada | Tecnologia | Justificativa |
|---|---|---|
| **API / Worker** | Node.js 20+ (Fastify) | Alta performance, SDK Google Maps oficial disponível |
| **Banco de Dados** | PostgreSQL 15+ com extensão **PostGIS** | Armazenamento e queries geoespaciais (distância, clustering) |
| **Cache** | Redis 7+ | Cache de geocodificação (TTL 30 dias) + lock para rotas |
| **Broker** | Redis Pub/Sub | Publicação de eventos de rota e ETA |
| **ORM** | Prisma (com PostGIS raw queries quando necessário) | Type-safe + suporte a geometrias via raw SQL |
| **Algoritmo TSP** | Google Maps Routes API (primary) + OR-Tools (fallback/offline) | OR-Tools é open-source do Google para otimização |
| **Geocodificação** | Google Maps Geocoding API + OpenStreetMap Nominatim | Primary + fallback gratuito |

---

## Padrão de Comunicação

```
Módulo 006 (ERP)
    └── Redis Broker → ROTA_CRIADA → MS-005 Consumer
            └── GeocodificacaoService → Redis Cache → Google Maps / OSM
            └── OtimizacaoService → Google Routes API / OR-Tools
            └── Redis Broker → ROTA_OTIMIZADA → Módulo 006 + App

App Entregador
    └── Redis Broker → LOCALIZACAO_ATUALIZADA → MS-005 Consumer
            └── EtaService → Recalculo ETAs
            └── Redis Broker → ETA_ATUALIZADO → App + Módulo 006
```

---

## Integração com Providers Geográficos

### Adapter de Geocodificação

```typescript
interface GeocodingProvider {
  geocode(endereco: string): Promise<GeocodeResult>;
  reverseGeocode(lat: number, lng: number): Promise<string>;
}

class GoogleMapsProvider implements GeocodingProvider {
  async geocode(endereco: string): Promise<GeocoderResult> { ... }
}

class OpenStreetMapProvider implements GeocodingProvider {
  async geocode(endereco: string): Promise<GeocoderResult> { ... }
}

class GeocodingService {
  async geocode(endereco: string): Promise<GeocoderResult> {
    const cacheKey = `geo:${hash(normalizar(endereco))}`;
    const cached = await redis.get(cacheKey);
    if (cached) return JSON.parse(cached);

    try {
      const result = await googleMaps.geocode(endereco);
      await redis.setex(cacheKey, 30 * 24 * 3600, JSON.stringify(result));
      return result;
    } catch {
      return await openStreetMap.geocode(endereco); // fallback
    }
  }
}
```

### Configuração do PostGIS

```sql
-- Extensão PostGIS habilitada no Postgres
CREATE EXTENSION IF NOT EXISTS postgis;

-- Coluna de geometria na tabela EnderecoGeocodificado
ALTER TABLE enderecos_geocodificados
  ADD COLUMN geoloc GEOGRAPHY(POINT, 4326);

-- Index geoespacial para queries de proximidade
CREATE INDEX idx_enderecos_geoloc ON enderecos_geocodificados USING GIST(geoloc);

-- Query de exemplo: endereços num raio de 5km
SELECT * FROM enderecos_geocodificados
WHERE ST_DWithin(geoloc, ST_MakePoint(-46.63, -23.55)::geography, 5000);
```

---

## Algoritmo de Otimização de Rotas

### Para N ≤ 15 paradas: Nearest Neighbor + 2-opt

```typescript
// Nearest Neighbor heuristic
function nearestNeighbor(points: Point[], start: Point): Point[] {
  const rota = [start];
  const remaining = [...points];
  while (remaining.length > 0) {
    const ultimo = rota[rota.length - 1];
    const proximo = remaining.reduce((min, p) =>
      distancia(ultimo, p) < distancia(ultimo, min) ? p : min
    );
    rota.push(proximo);
    remaining.splice(remaining.indexOf(proximo), 1);
  }
  return rota;
}

// 2-opt improvement
function twoOpt(rota: Point[]): Point[] {
  // melhoria iterativa trocando pares de arestas
  // até não haver mais melhorias
}
```

### Para N > 15 paradas: Google Maps Routes API

```typescript
// Usa Google Maps Routes API v2 com waypoints optimization
const response = await mapsClient.optimizeWaypoints({
  origin: filial,
  destination: filial,  // retorna para o ponto de partida
  waypoints: paradas.map(p => ({ location: p, stopover: true })),
  optimizeWaypoints: true,
  travelMode: 'DRIVING',
  drivingOptions: { departureTime: new Date(), trafficModel: 'BEST_GUESS' },
});
```

---

## Estrutura de Pastas

```
ms-005-geocoding/
├── src/
│   ├── routes/
│   │   ├── geocoding.routes.ts
│   │   ├── rotas.routes.ts
│   │   ├── localizacao.routes.ts
│   │   └── health.routes.ts
│   ├── providers/
│   │   ├── GeocodingProvider.interface.ts
│   │   ├── GoogleMapsProvider.ts
│   │   └── OpenStreetMapProvider.ts
│   ├── services/
│   │   ├── GeocodingService.ts      # Cache + provider fallback
│   │   ├── OtimizacaoService.ts     # TSP algorithms
│   │   ├── EtaService.ts            # Recalculo de ETAs
│   │   └── LocalizacaoService.ts   # Tracking do entregador
│   ├── consumers/
│   │   ├── RotaCriadaConsumer.ts
│   │   └── LocalizacaoConsumer.ts
│   ├── algorithms/
│   │   ├── nearest-neighbor.ts
│   │   └── two-opt.ts
│   ├── models/                     # Prisma models
│   └── server.ts
├── prisma/
│   └── schema.prisma
├── docker-compose.yml
└── .env.example
```

---

## Controle de Custos Google Maps API

```typescript
// Monitorar uso da API e acionar fallback preventivo
const LIMITE_DIARIO_GEOCODING = 10_000;  // requisições/dia
const LIMITE_ALERTA_PCT = 0.80;           // alertar com 80% usado

class GoogleMapsProvider {
  async geocode(endereco: string): Promise<GeocoderResult> {
    const usoHoje = await redis.get('google_maps:uso_hoje') ?? 0;

    if (Number(usoHoje) >= LIMITE_DIARIO_GEOCODING) {
      logger.warn('Google Maps API — limite diário atingido, usando OSM fallback');
      return openStreetMap.geocode(endereco);
    }

    if (Number(usoHoje) >= LIMITE_DIARIO_GEOCODING * LIMITE_ALERTA_PCT) {
      await broker.publish('LIMITE_API_ATINGINDO', { uso: usoHoje, limite: LIMITE_DIARIO_GEOCODING });
    }

    await redis.incr('google_maps:uso_hoje');
    await redis.expireat('google_maps:uso_hoje', proximaMeiaNoite());
    return this._callGoogleMapsApi(endereco);
  }
}
```

---

## Monitoramento e Alertas

| Métrica | Threshold |
|---|---|
| Cache hit rate geocodificação | < 60% após 30 dias (endereços repetidos não sendo cacheados) |
| Tempo de otimização de rota | p95 > 5s (para ≤ 20 paradas) |
| Uso da Google Maps API | > 80% do limite diário |
| Falhas de geocodificação (confiança low) | > 10% das requisições |
| Latência de recálculo de ETA | > 3s por evento de localização |
