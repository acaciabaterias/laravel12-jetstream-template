# Tasks: Módulo 009 - Orquestração Fiscal e Bancária

**Feature Branch**: `009-fiscal-bank-orchestrator`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T006, T010-T024
- **Automated Financial Microservices**: T007-T018, T021-T024
- **Integrated Fiscal Compliance**: T001-T018, T021-T024
- **Proactive Quality & Customer Service**: T009-T024

## Phase 1: Database Migrations (Tenant)

- [ ] T001: Criar migration `create_notas_fiscais_orquestradas_table`
- [ ] T002: Criar migration `create_boletos_orquestrados_table`
- [ ] T003: Criar migration `create_filas_contingencia_table`
- [ ] T004: Criar migration `create_cnab_remessas_table`
- [ ] T005: Criar migration `create_cnab_retorno_uploads_table`
- [ ] T006: Criar migration `create_audit_logs_table`

## Phase 2: Models, Services e Gateways

- [ ] T007: Criar Model `NotaFiscalOrquestrada`
- [ ] T008: Criar Model `BoletoOrquestrado`
- [ ] T009: Criar Model `FilaContingencia`
- [ ] T010: Criar Model `CnabRemessa`
- [ ] T011: Criar Model `CnabRetornoUpload`
- [ ] T012: Criar service `FiscalGatewayClient`
- [ ] T013: Criar service `BankGatewayClient`
- [ ] T014: Criar service `OrchestratorIdempotencyService`
- [ ] T015: Criar Trait `Auditable`

## Phase 3: Retry, Contingência e Painel

- [ ] T016: Criar job `RetryOrchestratorJob`
- [ ] T017: Implementar política de retry com backoff controlado
- [ ] T018: Criar Livewire component `FiscalContingencyDashboard`
- [ ] T019: Implementar alerta para contingência crítica

## Phase 4: CNAB e Uploads

- [ ] T020: Criar Livewire component `CnabUploadPanel`
- [ ] T021: Criar job `DispatchCnabProcessingJob`
- [ ] T022: Implementar upload de CNAB retorno com encaminhamento ao microserviço bancário
- [ ] T023: Implementar download e rastreio de remessas

## Phase 5: Tests

- [ ] T024: Testar retry e entrada em contingência após falha externa
- [ ] T025: Testar idempotência para evitar duplicidade de emissão
- [ ] T026: Testar upload inválido de CNAB sem quebrar o painel
- [ ] T027: Testar persistência correta de respostas fiscal e bancária
- [ ] T028: Testar auditoria das integrações críticas
- [ ] T029: Testar isolamento entre tenants sem cross-access
