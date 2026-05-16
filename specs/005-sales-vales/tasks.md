# Tasks: Módulo 005 - Vendas e Assistência (Vales e OS)

**Feature Branch**: `005-sales-service-os`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T006, T009-T020, T028-T035
- **RBAC**: T012-T017, T028-T035
- **Comprehensive Inventory & Reverse Logistics**: T007-T011, T018-T020, T029-T033
- **Proactive Quality & Customer Service**: T013-T017, T021-T035

## Phase 1: Database Migrations (Tenant)

- [x] T001: Criar migration `create_vales_table`
- [x] T002: Criar migration `create_itens_vale_table`
- [x] T003: Criar migration `create_pedidos_venda_table`
- [x] T004: Criar migration `create_ordens_servico_table`
- [x] T005: Criar migration `create_reservas_estoque_table`
- [x] T006: Criar migration `create_audit_logs_table`

## Phase 2: Models e Services

- [x] T007: Criar Model `Vale`
- [x] T008: Criar Model `ItemVale`
- [x] T009: Criar Model `PedidoVenda`
- [x] T010: Criar Model `OrdemServico`
- [x] T011: Criar Model `ReservaEstoque`
- [x] T012: Criar service `NetPriceCalculator`
- [x] T013: Criar service `ReservaEstoqueService`
- [x] T014: Criar Trait `Auditable`

## Phase 3: UI de Vales

- [x] T015: Criar Livewire component `ValeForm`
- [x] T016: Criar Livewire component `ValeList`
- [x] T017: Implementar adição e remoção reativa de itens no vale
- [x] T018: Implementar cálculo de Net Price em tempo real
- [x] T019: Implementar reserva imediata de estoque ao adicionar item
- [x] T020: Implementar estorno de reserva ao remover item ou cancelar vale

## Phase 4: Conversões e OS

- [x] T021: Criar job `ConvertValeToPedidoJob`
- [x] T022: Criar job `ConvertValeToOsJob`
- [x] T023: Criar Livewire component `ValeConversionActions`
- [x] T024: Criar Livewire component `OrdemServicoForm`
- [x] T025: Implementar conversão para pedido com saída definitiva de estoque
- [x] T026: Implementar conversão para ordem de serviço com vínculo ao vale

## Phase 5: Busca, Auditoria e Conta Sucata

- [x] T027: Implementar busca de vales por cliente, período, status e vendedor
- [x] T028: Implementar visualização móvel com cache read-only
- [x] T029: Integrar débito e crédito com conta sucata do cliente
- [x] T030: Registrar auditoria de criação, edição, cancelamento e conversão

## Phase 6: Tests

- [x] T031: Testar criação de vale com cálculo de sucata
- [x] T032: Testar reserva concorrente da última unidade
- [x] T033: Testar conversão de vale em pedido de venda
- [x] T034: Testar conversão de vale em ordem de serviço
- [x] T035: Testar cancelamento com estorno total das reservas
- [x] T036: Testar cliente sem conta sucata com criação automática
- [x] T037: Testar auditoria das operações críticas
- [x] T038: Testar isolamento entre tenants sem cross-access
