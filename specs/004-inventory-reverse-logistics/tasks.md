# Tasks: Módulo 004 - Estoque e Logística Reversa

**Feature Branch**: `004-inventory-reverse-logistics`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T006, T012-T027
- **Comprehensive Inventory & Reverse Logistics**: T001-T023
- **Proactive Quality**: T006, T013-T027

## Phase 1: Database Migrations (Tenant)

- [ ] T001: Criar migration `create_depositos_table`
- [ ] T002: Criar migration `create_estoque_movimentacoes_table`
- [ ] T003: Criar migration `create_estoque_saldos_table`
- [ ] T004: Criar migration `create_xml_importacoes_table`
- [ ] T005: Criar migration `create_conta_sucata_movimentacoes_table`
- [ ] T006: Criar migration `create_audit_logs_table`

## Phase 2: Models, Services e Jobs

- [ ] T007: Criar Model `Deposito`
- [ ] T008: Criar Model `EstoqueMovimentacao`
- [ ] T009: Criar Model `EstoqueSaldo`
- [ ] T010: Criar Model `XmlImportacao`
- [ ] T011: Criar Model `ContaSucataMovimentacao`
- [ ] T012: Criar service `EstoqueSaldoService` para consolidar saldos
- [ ] T013: Criar service `XmlNfeParser` para leitura de NF-e
- [ ] T014: Criar job `ProcessXmlImportJob`

## Phase 3: Estoque e Ajustes

- [ ] T015: Criar Livewire component `EstoqueDashboard`
- [ ] T016: Criar Livewire component `EstoqueAdjustmentForm`
- [ ] T017: Implementar entradas, saídas, transferências e ajustes manuais
- [ ] T018: Implementar bloqueio de estoque negativo
- [ ] T019: Exigir justificativa obrigatória em ajustes manuais

## Phase 4: XML e Conta Sucata

- [ ] T020: Criar Livewire component `XmlImportForm`
- [ ] T021: Implementar fluxo de importação com pausa para itens não mapeados
- [ ] T022: Criar Livewire component `ContaSucataDashboard`
- [ ] T023: Implementar débitos e créditos da conta sucata para clientes e fornecedores

## Phase 5: Shelf Life e Auditoria

- [ ] T024: Implementar cálculo e alerta de shelf life
- [ ] T025: Criar Trait `Auditable` para estoque e sucata
- [ ] T026: Registrar auditoria completa em movimentações críticas
- [ ] T027: Bloquear reprocessamento de XML já importado pela mesma `chave_nfe`

## Phase 6: Tests

- [ ] T028: Testar importação de XML com itens compatíveis
- [ ] T029: Testar pausa da importação para item não mapeado
- [ ] T030: Testar bloqueio de estoque negativo
- [ ] T031: Testar atualização correta de saldo consolidado
- [ ] T032: Testar ajuste manual com justificativa obrigatória
- [ ] T033: Testar conta sucata com débito e crédito
- [ ] T034: Testar alerta de shelf life
- [ ] T035: Testar auditoria de movimentações
- [ ] T036: Testar isolamento entre tenants sem cross-access
