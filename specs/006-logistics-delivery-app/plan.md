# Implementation Plan: Módulo 006 - Logística e App do Entregador

**Branch**: `006-logistics-delivery-app`
**Input**: Feature specification from `/specs/006-logistics-delivery-app/spec.md`

## Technical Context

**Stack**: Laravel 12, Livewire 4, Alpine.js, PWA com Service Worker, PostgreSQL (tenants), IndexedDB, WebSockets  
**Authentication**: Resolvida pelo módulo 002  
**Database Isolation**: `TenantConnectionMiddleware` do módulo 001

## Constitution Check

| Principle | Status | Evidence |
|-----------|--------|----------|
| Multi-Tenancy Isolado (v2.0.0) | PASS | Rotas e recebimentos vivem no banco do tenant, sem `filial_id` |
| Mobile-First Field Operations | PASS | PWA offline, sincronização, recebimentos e ajuste em campo |
| Comprehensive Inventory & Reverse Logistics | PASS | Integração com sucata e fechamento logístico |
| Proactive Quality & Customer Service | PASS | Rastreamento, auditoria e validação de encerramento |

## Database Structure (Tenant Database)

```sql
CREATE TABLE rotas_entrega (
    id BIGSERIAL PRIMARY KEY,
    entregador_id BIGINT NOT NULL REFERENCES users(id),
    data_rota DATE NOT NULL,
    status VARCHAR(30) NOT NULL,
    veiculo_id BIGINT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE pontos_entrega (
    id BIGSERIAL PRIMARY KEY,
    rota_entrega_id BIGINT NOT NULL REFERENCES rotas_entrega(id),
    vale_id BIGINT NOT NULL REFERENCES vales(id),
    cliente_id BIGINT NOT NULL,
    endereco_entrega TEXT NOT NULL,
    ordem_parada INT NOT NULL,
    status VARCHAR(30) NOT NULL,
    peso_sucata_coletado DECIMAL(12,2),
    observacao TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE recebimentos_moveis (
    id BIGSERIAL PRIMARY KEY,
    ponto_entrega_id BIGINT NOT NULL REFERENCES pontos_entrega(id),
    valor DECIMAL(12,2) NOT NULL,
    metodo_pagamento VARCHAR(30) NOT NULL,
    status_sincronizado VARCHAR(30) NOT NULL,
    comprovante_path TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE geolocalizacao_eventos (
    id BIGSERIAL PRIMARY KEY,
    rota_entrega_id BIGINT NOT NULL REFERENCES rotas_entrega(id),
    ponto_entrega_id BIGINT REFERENCES pontos_entrega(id),
    latitude DECIMAL(10,7) NOT NULL,
    longitude DECIMAL(10,7) NOT NULL,
    tipo_evento VARCHAR(30) NOT NULL,
    recorded_at TIMESTAMP NOT NULL
);

CREATE TABLE sync_eventos (
    id BIGSERIAL PRIMARY KEY,
    dispositivo_uuid VARCHAR(100) NOT NULL,
    entidade_tipo VARCHAR(50) NOT NULL,
    entidade_id BIGINT NOT NULL,
    payload JSONB NOT NULL,
    status VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id BIGINT NOT NULL,
    old_values JSONB,
    new_values JSONB,
    ip INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);
```

## Directory Structure

```text
app/
├── Models/
│   ├── RotaEntrega.php
│   ├── PontoEntrega.php
│   ├── RecebimentoMovel.php
│   ├── GeolocalizacaoEvento.php
│   ├── SyncEvento.php
│   └── AuditLog.php
├── Livewire/
│   ├── RoutePlanner.php
│   ├── LogisticsDashboard.php
│   └── DeliveryRouteScreen.php
├── Jobs/
│   └── SyncDeliveryEventsJob.php
├── Services/
│   ├── DeliverySyncService.php
│   └── RouteCloseValidator.php
└── Traits/
    └── Auditable.php

database/migrations/tenant/
├── 2026_xx_xx_000001_create_rotas_entrega_table.php
├── 2026_xx_xx_000002_create_pontos_entrega_table.php
├── 2026_xx_xx_000003_create_recebimentos_moveis_table.php
├── 2026_xx_xx_000004_create_geolocalizacao_eventos_table.php
├── 2026_xx_xx_000005_create_sync_eventos_table.php
└── 2026_xx_xx_000006_create_audit_logs_table.php
```

## Design Notes

- Este módulo NÃO deve usar `filial_id`, `branch_id`, `Global Scope`, `HasFilial` ou `MultiTenantScope`.
- Operações offline devem ser idempotentes e reconciliáveis ao sincronizar.
- Apenas eventos geográficos relevantes devem ser persistidos; o streaming em tempo real pode usar WebSocket sem escrever tudo no banco.
