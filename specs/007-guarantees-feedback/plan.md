# Implementation Plan: Módulo 007 - Garantias e Feedback

**Branch**: `007-guarantees-feedback`
**Input**: Feature specification from `/specs/007-guarantees-feedback/spec.md`

## Technical Context

**Stack**: Laravel 12, Livewire 4, PostgreSQL (tenants), Redis queues, PDF generation, HTTP gateway integration  
**Authentication**: Resolvida pelo módulo 002  
**Database Isolation**: `TenantConnectionMiddleware` do módulo 001

## Constitution Check

| Principle | Status | Evidence |
|-----------|--------|----------|
| Multi-Tenancy Isolado (v2.0.0) | PASS | Garantias, empréstimos e notificações vivem no banco do tenant, sem `filial_id` |
| Proactive Quality & Customer Service | PASS | OS, laudos, notificações e índice de retorno modelados |
| RBAC | PASS | Fluxos previstos para atendente, técnico e gestor |
| Comprehensive Inventory & Reverse Logistics | PASS | Empréstimo e devolução integram com o módulo 004 |

## Database Structure (Tenant Database)

```sql
CREATE TABLE ordens_servico_garantia (
    id BIGSERIAL PRIMARY KEY,
    cliente_id BIGINT NOT NULL,
    bateria_id BIGINT NOT NULL REFERENCES baterias(id),
    vale_original_id BIGINT,
    data_abertura TIMESTAMP NOT NULL,
    status VARCHAR(30) NOT NULL,
    laudo TEXT,
    resultado VARCHAR(30),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE baterias_emprestimo (
    id BIGSERIAL PRIMARY KEY,
    os_garantia_id BIGINT NOT NULL REFERENCES ordens_servico_garantia(id),
    bateria_usada_id BIGINT NOT NULL REFERENCES baterias(id),
    data_retirada TIMESTAMP NOT NULL,
    data_devolucao_prevista TIMESTAMP NOT NULL,
    data_devolucao_real TIMESTAMP NULL,
    termo_arquivo_path TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE notificacoes_whatsapp (
    id BIGSERIAL PRIMARY KEY,
    os_garantia_id BIGINT NOT NULL REFERENCES ordens_servico_garantia(id),
    cliente_telefone VARCHAR(30) NOT NULL,
    status VARCHAR(30) NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio TIMESTAMP NULL,
    identificador_externo VARCHAR(100),
    tracking_error TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE indices_retorno_produto (
    id BIGSERIAL PRIMARY KEY,
    bateria_id BIGINT NOT NULL REFERENCES baterias(id),
    periodo_inicio DATE NOT NULL,
    periodo_fim DATE NOT NULL,
    total_vendidas INT NOT NULL,
    total_garantias INT NOT NULL,
    indice_calculado DECIMAL(8,4) NOT NULL,
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
│   ├── OrdemServicoGarantia.php
│   ├── BateriaEmprestimo.php
│   ├── NotificacaoWhatsApp.php
│   ├── IndiceRetornoProduto.php
│   └── AuditLog.php
├── Livewire/
│   ├── GarantiaBoard.php
│   ├── GarantiaForm.php
│   └── GarantiaLaudoForm.php
├── Jobs/
│   └── SendGuaranteeWhatsAppNotificationJob.php
├── Services/
│   ├── GuaranteeChargeService.php
│   ├── LoanBatteryTermService.php
│   └── ReturnIndexService.php
└── Traits/
    └── Auditable.php

database/migrations/tenant/
├── 2026_xx_xx_000001_create_ordens_servico_garantia_table.php
├── 2026_xx_xx_000002_create_baterias_emprestimo_table.php
├── 2026_xx_xx_000003_create_notificacoes_whatsapp_table.php
├── 2026_xx_xx_000004_create_indices_retorno_produto_table.php
└── 2026_xx_xx_000005_create_audit_logs_table.php
```

## Design Notes

- Este módulo NÃO deve usar `filial_id`, `branch_id`, `Global Scope`, `HasFilial` ou `MultiTenantScope`.
- Envio de WhatsApp deve ser totalmente assíncrono e tolerante a falhas.
- O índice de retorno deve ser mantido por atualização incremental ou serviço dedicado, evitando cálculos pesados em tela.
