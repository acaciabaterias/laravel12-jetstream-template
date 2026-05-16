# Implementation Plan: Módulo 004 - Estoque e Logística Reversa

**Branch**: `004-inventory-reverse-logistics`
**Input**: Feature specification from `/specs/004-inventory-reverse-logistics/spec.md`

## Technical Context

**Stack**: Laravel 12, Livewire 4, PostgreSQL (tenants), Redis queues  
**Authentication**: Resolvida pelo módulo 002  
**Database Isolation**: `TenantConnectionMiddleware` do módulo 001

## Constitution Check

| Principle | Status | Evidence |
|-----------|--------|----------|
| Multi-Tenancy Isolado (v2.0.0) | PASS | Estoque e depósitos vivem no banco do tenant, sem `filial_id` |
| Comprehensive Inventory & Reverse Logistics | PASS | Movimentações, XML, shelf life e conta sucata modelados |
| Proactive Quality | PASS | Auditoria e rastreabilidade de operações críticas |

## Database Structure (Tenant Database)

```sql
CREATE TABLE depositos (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE estoque_movimentacoes (
    id BIGSERIAL PRIMARY KEY,
    bateria_id BIGINT NOT NULL REFERENCES baterias(id),
    deposito_id BIGINT NOT NULL REFERENCES depositos(id),
    user_id BIGINT NOT NULL REFERENCES users(id),
    tipo_operacao VARCHAR(50) NOT NULL,
    origem VARCHAR(50) NOT NULL,
    quantidade DECIMAL(12,2) NOT NULL,
    justificativa TEXT,
    data_movimentacao TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE estoque_saldos (
    id BIGSERIAL PRIMARY KEY,
    bateria_id BIGINT NOT NULL REFERENCES baterias(id),
    deposito_id BIGINT NOT NULL REFERENCES depositos(id),
    quantidade_atual DECIMAL(12,2) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE (bateria_id, deposito_id)
);

CREATE TABLE xml_importacoes (
    id BIGSERIAL PRIMARY KEY,
    chave_nfe VARCHAR(60) NOT NULL UNIQUE,
    fornecedor_id BIGINT,
    status VARCHAR(30) NOT NULL,
    log_erros JSONB,
    payload_xml TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE conta_sucata_movimentacoes (
    id BIGSERIAL PRIMARY KEY,
    entidade_tipo VARCHAR(30) NOT NULL,
    entidade_id BIGINT NOT NULL,
    tipo_movimento VARCHAR(20) NOT NULL,
    quantidade_kg DECIMAL(12,2) NOT NULL,
    valor_unitario DECIMAL(12,2),
    saldo_resultante DECIMAL(12,2) NOT NULL,
    origem VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
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
│   ├── Deposito.php
│   ├── EstoqueMovimentacao.php
│   ├── EstoqueSaldo.php
│   ├── XmlImportacao.php
│   ├── ContaSucataMovimentacao.php
│   └── AuditLog.php
├── Livewire/
│   ├── EstoqueDashboard.php
│   ├── EstoqueAdjustmentForm.php
│   ├── XmlImportForm.php
│   └── ContaSucataDashboard.php
├── Jobs/
│   └── ProcessXmlImportJob.php
├── Services/
│   ├── XmlNfeParser.php
│   └── EstoqueSaldoService.php
└── Traits/
    └── Auditable.php

database/migrations/tenant/
├── 2026_xx_xx_000001_create_depositos_table.php
├── 2026_xx_xx_000002_create_estoque_movimentacoes_table.php
├── 2026_xx_xx_000003_create_estoque_saldos_table.php
├── 2026_xx_xx_000004_create_xml_importacoes_table.php
├── 2026_xx_xx_000005_create_conta_sucata_movimentacoes_table.php
└── 2026_xx_xx_000006_create_audit_logs_table.php
```

## Design Notes

- O módulo 001 já resolve a conexão do tenant; este módulo NÃO deve usar `filial_id`, `branch_id`, `MultiTenantScope` ou middleware de contexto.
- Os saldos consolidados são derivados do extrato de movimentações e protegidos contra estoque negativo.
- O processamento de XML deve suportar fila para arquivos maiores e prevenção de reprocessamento duplicado.
