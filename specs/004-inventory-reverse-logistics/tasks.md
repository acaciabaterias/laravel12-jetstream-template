# Tasks: Módulo 004 - Estoque e Logística Reversa

**Feature Branch**: `004-inventory-reverse-logistics`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T006, T012-T027
- **Comprehensive Inventory & Reverse Logistics**: T001-T023
- **Proactive Quality**: T006, T013-T027

## Phase 1: Database Migrations (Tenant)

- [x] T001: Criar migration `create_depositos_table`
- [x] T002: Criar migration `create_estoque_movimentacoes_table`
- [x] T003: Criar migration `create_estoque_saldos_table`
- [x] T004: Criar migration `create_xml_importacoes_table`
- [x] T005: Criar migration `create_conta_sucata_movimentacoes_table`
- [x] T006: Criar migration `create_audit_logs_table`

## Phase 2: Models, Services e Jobs

- [x] T007: Criar Model `Deposito`
- [x] T008: Criar Model `EstoqueMovimentacao`
- [x] T009: Criar Model `EstoqueSaldo`
- [x] T010: Criar Model `XmlImportacao`
- [x] T011: Criar Model `ContaSucataMovimentacao`
- [x] T012: Criar service `EstoqueSaldoService` para consolidar saldos
- [x] T013: Criar service `XmlNfeParser` para leitura de NF-e
- [x] T014: Criar job `ProcessXmlImportJob`

## Phase 3: Estoque e Ajustes

- [x] T015: Criar Livewire component `EstoqueDashboard`
- [x] T016: Criar Livewire component `EstoqueAdjustmentForm`
- [x] T017: Implementar entradas, saídas, transferências e ajustes manuais
- [x] T018: Implementar bloqueio de estoque negativo
- [x] T019: Exigir justificativa obrigatória em ajustes manuais

## Phase 4: XML e Conta Sucata

- [x] T020: Criar Livewire component `XmlImportForm`
- [x] T021: Implementar fluxo de importação com pausa para itens não mapeados
- [x] T022: Criar Livewire component `ContaSucataDashboard`
- [x] T023: Implementar débitos e créditos da conta sucata para clientes e fornecedores

## Phase 5: Shelf Life e Auditoria

- [x] T024: Implementar cálculo e alerta de shelf life
- [x] T025: Criar Trait `Auditable` para estoque e sucata
- [x] T026: Registrar auditoria completa em movimentações críticas
- [x] T027: Bloquear reprocessamento de XML já importado pela mesma `chave_nfe`

## Phase 6: Tests

- [x] T028: Testar importação de XML com itens compatíveis
- [x] T029: Testar pausa da importação para item não mapeado
- [x] T030: Testar bloqueio de estoque negativo
- [x] T031: Testar atualização correta de saldo consolidado
- [x] T032: Testar ajuste manual com justificativa obrigatória
- [x] T033: Testar conta sucata com débito e crédito
- [x] T034: Testar alerta de shelf life
- [x] T035: Testar auditoria de movimentações
- [x] T036: Testar isolamento entre tenants sem cross-access
