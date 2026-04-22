# Implementation Plan: Módulo 005 - Vendas e Assistência (Vales e OS)

**Branch**: `005-sales-service-os`
**Input**: Feature specification from `/specs/005-sales-vales/spec.md`

## Technical Context

**Stack**: Laravel 12, Livewire 4, PostgreSQL (tenants), Redis queues  
**Authentication**: Resolvida pelo módulo 002  
**Database Isolation**: `TenantConnectionMiddleware` do módulo 001

## Constitution Check

| Principle | Status | Evidence |
|-----------|--------|----------|
| Multi-Tenancy Isolado (v2.0.0) | PASS | Vales, pedidos e OS vivem no banco do tenant, sem `filial_id` |
| RBAC | PASS | Fluxos previstos por vendedor, gestor e tecnico |
| Comprehensive Inventory & Reverse Logistics | PASS | Reservas de estoque e integração com conta sucata |
| Proactive Quality & Customer Service | PASS | Conversão para OS e rastreabilidade completa |

## Database Structure (Tenant Database)

```sql
CREATE TABLE vales (
    id BIGSERIAL PRIMARY KEY,
    cliente_id BIGINT NOT NULL,
    vendedor_id BIGINT NOT NULL REFERENCES users(id),
    status VARCHAR(30) NOT NULL,
    data_criacao TIMESTAMP NOT NULL,
    data_faturamento TIMESTAMP NULL,
    observacoes TEXT,
    created_by BIGINT NOT NULL REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE itens_vale (
    id BIGSERIAL PRIMARY KEY,
    vale_id BIGINT NOT NULL REFERENCES vales(id),
    bateria_id BIGINT NOT NULL REFERENCES baterias(id),
    quantidade DECIMAL(12,2) NOT NULL,
    preco_unitario_original DECIMAL(12,2) NOT NULL,
    preco_unitario_final DECIMAL(12,2) NOT NULL,
    flag_devolveu_sucata BOOLEAN NOT NULL DEFAULT TRUE,
    observacao TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE pedidos_venda (
    id BIGSERIAL PRIMARY KEY,
    vale_id BIGINT NOT NULL REFERENCES vales(id),
    cliente_id BIGINT NOT NULL,
    data_emissao TIMESTAMP NOT NULL,
    valor_total DECIMAL(12,2) NOT NULL,
    status VARCHAR(30) NOT NULL,
    nf_referencia VARCHAR(60),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE ordens_servico (
    id BIGSERIAL PRIMARY KEY,
    vale_id BIGINT NOT NULL REFERENCES vales(id),
    cliente_id BIGINT NOT NULL,
    tecnico_responsavel_id BIGINT REFERENCES users(id),
    data_abertura TIMESTAMP NOT NULL,
    status VARCHAR(30) NOT NULL,
    laudo TEXT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE reservas_estoque (
    id BIGSERIAL PRIMARY KEY,
    vale_id BIGINT NOT NULL REFERENCES vales(id),
    item_vale_id BIGINT NOT NULL REFERENCES itens_vale(id),
    bateria_id BIGINT NOT NULL REFERENCES baterias(id),
    quantidade DECIMAL(12,2) NOT NULL,
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
│   ├── Vale.php
│   ├── ItemVale.php
│   ├── PedidoVenda.php
│   ├── OrdemServico.php
│   ├── ReservaEstoque.php
│   └── AuditLog.php
├── Livewire/
│   ├── ValeForm.php
│   ├── ValeList.php
│   ├── ValeConversionActions.php
│   └── OrdemServicoForm.php
├── Jobs/
│   ├── ConvertValeToPedidoJob.php
│   └── ConvertValeToOsJob.php
├── Services/
│   ├── NetPriceCalculator.php
│   └── ReservaEstoqueService.php
└── Traits/
    └── Auditable.php

database/migrations/tenant/
├── 2026_xx_xx_000001_create_vales_table.php
├── 2026_xx_xx_000002_create_itens_vale_table.php
├── 2026_xx_xx_000003_create_pedidos_venda_table.php
├── 2026_xx_xx_000004_create_ordens_servico_table.php
├── 2026_xx_xx_000005_create_reservas_estoque_table.php
└── 2026_xx_xx_000006_create_audit_logs_table.php
```

## Design Notes

- Este módulo NÃO deve usar `filial_id`, `branch_id`, `Global Scope`, `HasFilial` ou `MultiTenantScope`.
- Toda reserva e confirmação de estoque deve operar em transação com o módulo 004.
- Conversões para pedido e OS devem manter rastreabilidade completa do vale original.
