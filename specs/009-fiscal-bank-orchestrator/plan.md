# Implementation Plan: Módulo 009 - Orquestração Fiscal e Bancária

**Branch**: `009-fiscal-bank-orchestrator`
**Input**: Feature specification from `/specs/009-fiscal-bank-orchestrator/spec.md`

## Technical Context

**Stack**: Laravel 12, HTTP Client, PostgreSQL (tenants), Redis queues, Horizon, storage para XML/PDF/CNAB  
**Authentication**: Resolvida pelo módulo 002  
**Database Isolation**: `TenantConnectionMiddleware` do módulo 001

## Constitution Check

| Principle | Status | Evidence |
|-----------|--------|----------|
| Multi-Tenancy Isolado (v2.0.0) | PASS | Emissões e contingências vivem no banco do tenant, sem `filial_id` |
| Automated Financial Microservices | PASS | Integração por gateway com microserviços externos |
| Integrated Fiscal Compliance | PASS | Emissão fiscal e CNAB delegados com rastreabilidade |
| Proactive Quality & Customer Service | PASS | Retry, contingência e status operacional explícito |

## Database Structure (Tenant Database)

```sql
CREATE TABLE notas_fiscais_orquestradas (
    id BIGSERIAL PRIMARY KEY,
    vale_id BIGINT NOT NULL REFERENCES vales(id),
    chave_acesso VARCHAR(80),
    xml_path TEXT,
    status VARCHAR(30) NOT NULL,
    ms_requisicao_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE boletos_orquestrados (
    id BIGSERIAL PRIMARY KEY,
    vale_id BIGINT NOT NULL REFERENCES vales(id),
    nosso_numero VARCHAR(100),
    linha_digitavel VARCHAR(255),
    pdf_url TEXT,
    status VARCHAR(30) NOT NULL,
    identificador_externo VARCHAR(100),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE filas_contingencia (
    id BIGSERIAL PRIMARY KEY,
    tipo_integracao VARCHAR(30) NOT NULL,
    payload JSONB NOT NULL,
    tentativas INT NOT NULL DEFAULT 0,
    proxima_tentativa TIMESTAMP,
    status VARCHAR(30) NOT NULL,
    ultimo_erro TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE cnab_remessas (
    id BIGSERIAL PRIMARY KEY,
    tipo_arquivo VARCHAR(20) NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    status VARCHAR(30) NOT NULL,
    arquivo_path TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE cnab_retorno_uploads (
    id BIGSERIAL PRIMARY KEY,
    cnab_remessa_id BIGINT REFERENCES cnab_remessas(id),
    nome_arquivo VARCHAR(255) NOT NULL,
    status_processamento VARCHAR(30) NOT NULL,
    log_processamento TEXT,
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
│   ├── NotaFiscalOrquestrada.php
│   ├── BoletoOrquestrado.php
│   ├── FilaContingencia.php
│   ├── CnabRemessa.php
│   ├── CnabRetornoUpload.php
│   └── AuditLog.php
├── Livewire/
│   ├── FiscalContingencyDashboard.php
│   └── CnabUploadPanel.php
├── Jobs/
│   ├── RetryOrchestratorJob.php
│   └── DispatchCnabProcessingJob.php
├── Services/
│   ├── FiscalGatewayClient.php
│   ├── BankGatewayClient.php
│   └── OrchestratorIdempotencyService.php
└── Traits/
    └── Auditable.php

database/migrations/tenant/
├── 2026_xx_xx_000001_create_notas_fiscais_orquestradas_table.php
├── 2026_xx_xx_000002_create_boletos_orquestrados_table.php
├── 2026_xx_xx_000003_create_filas_contingencia_table.php
├── 2026_xx_xx_000004_create_cnab_remessas_table.php
├── 2026_xx_xx_000005_create_cnab_retorno_uploads_table.php
└── 2026_xx_xx_000006_create_audit_logs_table.php
```

## Design Notes

- Este módulo NÃO deve usar `filial_id`, `branch_id`, `Global Scope`, `HasFilial` ou `MultiTenantScope`.
- O orquestrador apenas delega e persiste respostas; lógica fiscal e bancária de domínio permanece nos microserviços externos.
- Retry e contingência devem ser idempotentes e observáveis.
