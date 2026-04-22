# Implementation Plan: Módulo 002 - RBAC (Database-per-Client)

**Branch**: `002-users-permissions-rbac`
**Input**: Feature specification from `/specs/002-users-permissions-rbac/spec.md`

## Technical Context

**Stack**: Laravel 12, Livewire 4, PostgreSQL (tenants), Supabase (central)  
**Authentication**: Laravel Fortify + Session dentro do tenant  
**Authorization**: Gates e Policies baseados em papel

## Constitution Check

| Principle | Status | Evidence |
|-----------|--------|----------|
| Multi-Tenancy Isolado (v2.0.0) | PASS | `users` por tenant, sem `filial_id` |
| RBAC | PASS | Papéis e permissões implementados no tenant |
| Auditoria de Acesso | PASS | `audit_logs_acesso` registra IP, dispositivo e timestamp |
| Database-per-client | PASS | Conexão resolvida via `TenantConnectionMiddleware` |

## Database Structure

### Banco Central (`usuarios_plataforma`)

```sql
CREATE TABLE usuarios_plataforma (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    papel VARCHAR(50) NOT NULL CHECK (papel IN ('super_admin', 'support', 'billing')),
    created_at TIMESTAMP DEFAULT NOW()
);
```

### Banco do Tenant (`users`, `permissoes`, `papel_permissao`, `audit_logs_acesso`)

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    papel VARCHAR(50) NOT NULL CHECK (papel IN ('dono', 'gestor', 'vendedor', 'tecnico', 'estoquista', 'entregador')),
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_login TIMESTAMP NULL,
    ultimo_ip INET NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE permissoes (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    descricao TEXT
);

CREATE TABLE papel_permissao (
    papel VARCHAR(50) NOT NULL,
    permissao_id BIGINT NOT NULL REFERENCES permissoes(id),
    PRIMARY KEY (papel, permissao_id)
);

CREATE TABLE audit_logs_acesso (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    ip INET NOT NULL,
    user_agent TEXT,
    sucesso BOOLEAN NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);
```

## Directory Structure

```text
app/
├── Http/
│   ├── Middleware/
│   │   └── TenantConnectionMiddleware.php
│   └── Controllers/
│       └── Auth/
├── Models/
│   ├── User.php
│   ├── Permissao.php
│   └── AuditLogAcesso.php
├── Policies/
│   └── UserPolicy.php
└── Livewire/
    ├── UserManager.php
    └── UserForm.php

database/migrations/tenant/
├── 2026_xx_xx_000001_create_users_table.php
├── 2026_xx_xx_000002_create_permissoes_table.php
├── 2026_xx_xx_000003_create_papel_permissao_table.php
└── 2026_xx_xx_000004_create_audit_logs_acesso_table.php
```

## Design Notes

- O módulo 001 já fornece o `TenantConnectionMiddleware`; este módulo NÃO deve criar novo middleware de isolamento.
- A autenticação operacional acontece sempre no banco do tenant já resolvido.
- Usuários de plataforma vivem apenas no banco central e não substituem usuários operacionais do tenant.
