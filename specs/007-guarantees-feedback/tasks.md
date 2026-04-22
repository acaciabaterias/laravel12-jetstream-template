# Tasks: Módulo 007 - Garantias e Feedback

**Feature Branch**: `007-guarantees-feedback`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T005, T010-T026
- **Proactive Quality & Customer Service**: T006-T026
- **RBAC**: T006-T014, T021-T026
- **Comprehensive Inventory & Reverse Logistics**: T007-T009, T015-T020, T022-T024

## Phase 1: Database Migrations (Tenant)

- [ ] T001: Criar migration `create_ordens_servico_garantia_table`
- [ ] T002: Criar migration `create_baterias_emprestimo_table`
- [ ] T003: Criar migration `create_notificacoes_whatsapp_table`
- [ ] T004: Criar migration `create_indices_retorno_produto_table`
- [ ] T005: Criar migration `create_audit_logs_table`

## Phase 2: Models, Services e Policies

- [ ] T006: Criar Model `OrdemServicoGarantia`
- [ ] T007: Criar Model `BateriaEmprestimo`
- [ ] T008: Criar Model `NotificacaoWhatsApp`
- [ ] T009: Criar Model `IndiceRetornoProduto`
- [ ] T010: Criar service `LoanBatteryTermService`
- [ ] T011: Criar service `GuaranteeChargeService`
- [ ] T012: Criar service `ReturnIndexService`
- [ ] T013: Criar Trait `Auditable`
- [ ] T014: Configurar Policies/Gates para atendimento, técnico e gestão

## Phase 3: Fluxo de Garantia e Laudo

- [ ] T015: Criar Livewire component `GarantiaBoard`
- [ ] T016: Criar Livewire component `GarantiaForm`
- [ ] T017: Criar Livewire component `GarantiaLaudoForm`
- [ ] T018: Implementar abertura de OS vinculada ou avulsa
- [ ] T019: Implementar bateria de empréstimo com geração de termo PDF
- [ ] T020: Implementar cobrança de improcedência

## Phase 4: WhatsApp e KPI

- [ ] T021: Criar job `SendGuaranteeWhatsAppNotificationJob`
- [ ] T022: Implementar disparo assíncrono em mudanças de status
- [ ] T023: Implementar tratamento resiliente de falha de envio
- [ ] T024: Atualizar índice de retorno do produto com base em vendas e garantias

## Phase 5: Tests

- [ ] T025: Testar abertura de garantia com bateria de empréstimo
- [ ] T026: Testar laudo improcedente com cobrança associada
- [ ] T027: Testar falha do WhatsApp sem quebrar a OS
- [ ] T028: Testar atualização do índice de retorno do produto
- [ ] T029: Testar alerta para empréstimo vencido
- [ ] T030: Testar isolamento entre tenants sem cross-access
