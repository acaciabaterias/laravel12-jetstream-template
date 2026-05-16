# Implementation Plan: Módulo 008 - Financeiro Inteligente

**Branch**: `008-intelligent-finance`
**Input**: Feature specification from `/specs/008-intelligent-finance/spec.md`

## Technical Context

**Stack**: Laravel 12, Livewire 4, PostgreSQL (tenants), Redis queues, HTTP integrations, analytical views/materialized data  
**Authentication**: Resolvida pelo módulo 002  
**Database Isolation**: `TenantConnectionMiddleware` do módulo 001

## Constitution Check

| Principle | Status | Evidence |
|-----------|--------|----------|
| Multi-Tenancy Isolado (v2.0.0) | PASS | Contas e transações financeiras vivem no banco do tenant, sem `filial_id` |
| Automated Financial Microservices | PASS | Integração bancária, conciliação e jobs assíncronos modelados |
| Proactive Quality & Customer Service | PASS | Cobrança automática de improcedência e alertas de falha |
| RBAC | PASS | Fluxos financeiros previstos com controle de acesso |

## Database Structure (Tenant Database)

```sql
CREATE TABLE contas_bancarias (
    id BIGSERIAL PRIMARY KEY,
    banco VARCHAR(100) NOT NULL,
    agencia VARCHAR(30) NOT NULL,
    conta VARCHAR(50) NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    token_api TEXT,
    status VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE transacoes_financeiras (
    id BIGSERIAL PRIMARY KEY,
    conta_bancaria_id BIGINT NOT NULL REFERENCES contas_bancarias(id),
    tipo VARCHAR(30) NOT NULL,
    valor DECIMAL(12,2) NOT NULL,
    data_transacao TIMESTAMP NOT NULL,
    status_conciliado VARCHAR(30) NOT NULL,
    origem_tipo VARCHAR(50),
    origem_id BIGINT,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE fluxo_caixa_projetado (
    id BIGSERIAL PRIMARY KEY,
    data_referencia DATE NOT NULL,
    saldo_inicial DECIMAL(12,2) NOT NULL,
    total_receber DECIMAL(12,2) NOT NULL,
    total_pagar DECIMAL(12,2) NOT NULL,
    saldo_projetado DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE margens_lucro_reais (
    id BIGSERIAL PRIMARY KEY,
    bateria_id BIGINT NOT NULL REFERENCES baterias(id),
    periodo_inicio DATE NOT NULL,
    periodo_fim DATE NOT NULL,
    valor_venda DECIMAL(12,2) NOT NULL,
    custo_aquisicao DECIMAL(12,2) NOT NULL,
    frete DECIMAL(12,2) DEFAULT 0,
    imposto DECIMAL(12,2) DEFAULT 0,
    comissao DECIMAL(12,2) DEFAULT 0,
    margem_calculada DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE conciliacoes_pendentes (
    id BIGSERIAL PRIMARY KEY,
    transacao_financeira_id BIGINT NOT NULL REFERENCES transacoes_financeiras(id),
    motivo TEXT NOT NULL,
    payload_bancario JSONB,
    status VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE fechamentos_contabeis (
    id BIGSERIAL PRIMARY KEY,
    competencia VARCHAR(7) NOT NULL UNIQUE,
    status VARCHAR(30) NOT NULL,
    fechado_em TIMESTAMP,
    fechado_por BIGINT,
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
│   ├── ContaBancaria.php
│   ├── TransacaoFinanceira.php
│   ├── FluxoCaixaProjetado.php
│   ├── MargemLucroReal.php
│   ├── ConciliacaoPendente.php
│   ├── FechamentoContabil.php
│   └── AuditLog.php
├── Livewire/
│   ├── FinanceDashboard.php
│   ├── CashFlowPanel.php
│   └── MarginAnalysisGrid.php
├── Jobs/
│   ├── SyncBankTransactionsJob.php
│   └── RebuildReturnChargeJob.php
├── Services/
│   ├── BankApiClient.php
│   ├── FinanceMatcherProcessor.php
│   └── ClosingPeriodGuard.php
└── Traits/
    └── Auditable.php

database/migrations/tenant/
├── 2026_xx_xx_000001_create_contas_bancarias_table.php
├── 2026_xx_xx_000002_create_transacoes_financeiras_table.php
├── 2026_xx_xx_000003_create_fluxo_caixa_projetado_table.php
├── 2026_xx_xx_000004_create_margens_lucro_reais_table.php
├── 2026_xx_xx_000005_create_conciliacoes_pendentes_table.php
├── 2026_xx_xx_000006_create_fechamentos_contabeis_table.php
└── 2026_xx_xx_000007_create_audit_logs_table.php
```

## Design Notes

- Este módulo NÃO deve usar `filial_id`, `branch_id`, `Global Scope`, `HasFilial` ou `MultiTenantScope`.
- Integrações bancárias devem ser assíncronas e resilientes a falhas de token ou API.
- Cálculos analíticos pesados podem usar visão materializada, tabela agregada ou rebuild assíncrono, mas sempre dentro do banco do tenant.
