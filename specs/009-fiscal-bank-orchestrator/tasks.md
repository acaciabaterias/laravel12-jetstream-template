# Tasks: Módulo 009 - Orquestração Fiscal e Bancária

**Feature Branch**: `009-fiscal-bank-orchestrator`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T006, T010-T024
- **Automated Financial Microservices**: T007-T018, T021-T024
- **Integrated Fiscal Compliance**: T001-T018, T021-T024
- **Proactive Quality & Customer Service**: T009-T024

## Phase 1: Database Migrations (Tenant)

- [x] T001: Criar migration `create_notas_fiscais_orquestradas_table`
- [x] T002: Criar migration `create_boletos_orquestrados_table`
- [x] T003: Criar migration `create_filas_contingencia_table`
- [x] T004: Criar migration `create_cnab_remessas_table`
- [x] T005: Criar migration `create_cnab_retorno_uploads_table`
- [x] T006: Criar migration `create_audit_logs_table`

## Phase 2: Models, Services e Gateways

- [x] T007: Criar Model `NotaFiscalOrquestrada`
- [x] T008: Criar Model `BoletoOrquestrado`
- [x] T009: Criar Model `FilaContingencia`
- [x] T010: Criar Model `CnabRemessa`
- [x] T011: Criar Model `CnabRetornoUpload`
- [x] T012: Criar service `FiscalGatewayClient`
- [x] T013: Criar service `BankGatewayClient`
- [x] T014: Criar service `OrchestratorIdempotencyService`
- [x] T015: Criar Trait `Auditable`

## Phase 3: Retry, Contingência e Painel

- [x] T016: Criar job `RetryOrchestratorJob`
- [x] T017: Implementar política de retry com backoff controlado
- [x] T018: Criar Livewire component `FiscalContingencyDashboard`
- [x] T019: Implementar alerta para contingência crítica

## Phase 4: CNAB e Uploads

- [x] T020: Criar Livewire component `CnabUploadPanel`
- [x] T021: Criar job `DispatchCnabProcessingJob`
- [x] T022: Implementar upload de CNAB retorno com encaminhamento ao microserviço bancário
- [x] T023: Implementar download e rastreio de remessas

## Phase 5: Tests

- [x] T024: Testar retry e entrada em contingência após falha externa
- [x] T025: Testar idempotência para evitar duplicidade de emissão
- [x] T026: Testar upload inválido de CNAB sem quebrar o painel
- [x] T027: Testar persistência correta de respostas fiscal e bancária
- [x] T028: Testar auditoria das integrações críticas
- [x] T029: Testar isolamento entre tenants sem cross-access
