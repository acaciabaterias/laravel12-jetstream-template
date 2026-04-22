# Implementation Plan: Módulo 003 - Cadastros Estruturais

**Branch**: `003-structural-registries`
**Input**: Feature specification from `/specs/003-structural-registries/spec.md`

## Technical Context

**Stack**: Laravel 12, Livewire 4, PostgreSQL (tenants)  
**Authentication**: Resolvida pelo módulo 002  
**Database Isolation**: `TenantConnectionMiddleware` do módulo 001

## Constitution Check

| Principle | Status | Evidence |
|-----------|--------|----------|
| Multi-Tenancy Isolado (v2.0.0) | PASS | Todas as entidades vivem no banco do tenant, sem `filial_id` |
| Business Domain Specialization | PASS | Entidades específicas do domínio de baterias e aplicações |
| Proactive Quality | PASS | `audit_logs` com rastreabilidade completa |

## Database Structure (Tenant Database)

```sql
CREATE TABLE fabricantes (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    codigo VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE veiculos (
    id BIGSERIAL PRIMARY KEY,
    fabricante_id BIGINT NOT NULL REFERENCES fabricantes(id),
    modelo VARCHAR(100) NOT NULL,
    ano_inicio INT,
    ano_fim INT,
    motorizacao VARCHAR(50),
    atributos_dinamicos JSONB,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE baterias (
    id BIGSERIAL PRIMARY KEY,
    sku VARCHAR(50) NOT NULL UNIQUE,
    marca VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    atributos_dinamicos JSONB,
    peso_sucata_kg DECIMAL(10,2),
    valor_base_sucata_kg DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE aplicacoes (
    id BIGSERIAL PRIMARY KEY,
    veiculo_id BIGINT NOT NULL REFERENCES veiculos(id),
    bateria_id BIGINT NOT NULL REFERENCES baterias(id),
    observacao TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(veiculo_id, bateria_id)
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
│   ├── Fabricante.php
│   ├── Veiculo.php
│   ├── Bateria.php
│   ├── Aplicacao.php
│   └── AuditLog.php
├── Livewire/
│   ├── FabricanteManager.php
│   ├── VeiculoManager.php
│   ├── BateriaManager.php
│   └── ApplicationManager.php
└── Traits/
    └── Auditable.php

database/migrations/tenant/
├── 2026_xx_xx_000001_create_fabricantes_table.php
├── 2026_xx_xx_000002_create_veiculos_table.php
├── 2026_xx_xx_000003_create_baterias_table.php
├── 2026_xx_xx_000004_create_aplicacoes_table.php
└── 2026_xx_xx_000005_create_audit_logs_table.php
```

## Design Notes

- O módulo 001 já resolve o banco correto; este módulo NÃO deve criar `MultiTenantScope` nem middleware de tenant.
- Todas as entidades estruturais vivem no banco do tenant atual.
- A auditoria deve registrar operações estruturais sem expor dados entre tenants.
