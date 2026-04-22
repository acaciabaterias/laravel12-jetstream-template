# Tasks: Módulo 005 - Vendas e Assistência (Vales e OS)

**Feature Branch**: `005-sales-service-os`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T006, T009-T020, T028-T035
- **RBAC**: T012-T017, T028-T035
- **Comprehensive Inventory & Reverse Logistics**: T007-T011, T018-T020, T029-T033
- **Proactive Quality & Customer Service**: T013-T017, T021-T035

## Phase 1: Database Migrations (Tenant)

- [ ] T001: Criar migration `create_vales_table`
- [ ] T002: Criar migration `create_itens_vale_table`
- [ ] T003: Criar migration `create_pedidos_venda_table`
- [ ] T004: Criar migration `create_ordens_servico_table`
- [ ] T005: Criar migration `create_reservas_estoque_table`
- [ ] T006: Criar migration `create_audit_logs_table`

## Phase 2: Models e Services

- [ ] T007: Criar Model `Vale`
- [ ] T008: Criar Model `ItemVale`
- [ ] T009: Criar Model `PedidoVenda`
- [ ] T010: Criar Model `OrdemServico`
- [ ] T011: Criar Model `ReservaEstoque`
- [ ] T012: Criar service `NetPriceCalculator`
- [ ] T013: Criar service `ReservaEstoqueService`
- [ ] T014: Criar Trait `Auditable`

## Phase 3: UI de Vales

- [ ] T015: Criar Livewire component `ValeForm`
- [ ] T016: Criar Livewire component `ValeList`
- [ ] T017: Implementar adição e remoção reativa de itens no vale
- [ ] T018: Implementar cálculo de Net Price em tempo real
- [ ] T019: Implementar reserva imediata de estoque ao adicionar item
- [ ] T020: Implementar estorno de reserva ao remover item ou cancelar vale

## Phase 4: Conversões e OS

- [ ] T021: Criar job `ConvertValeToPedidoJob`
- [ ] T022: Criar job `ConvertValeToOsJob`
- [ ] T023: Criar Livewire component `ValeConversionActions`
- [ ] T024: Criar Livewire component `OrdemServicoForm`
- [ ] T025: Implementar conversão para pedido com saída definitiva de estoque
- [ ] T026: Implementar conversão para ordem de serviço com vínculo ao vale

## Phase 5: Busca, Auditoria e Conta Sucata

- [ ] T027: Implementar busca de vales por cliente, período, status e vendedor
- [ ] T028: Implementar visualização móvel com cache read-only
- [ ] T029: Integrar débito e crédito com conta sucata do cliente
- [ ] T030: Registrar auditoria de criação, edição, cancelamento e conversão

## Phase 6: Tests

- [ ] T031: Testar criação de vale com cálculo de sucata
- [ ] T032: Testar reserva concorrente da última unidade
- [ ] T033: Testar conversão de vale em pedido de venda
- [ ] T034: Testar conversão de vale em ordem de serviço
- [ ] T035: Testar cancelamento com estorno total das reservas
- [ ] T036: Testar cliente sem conta sucata com criação automática
- [ ] T037: Testar auditoria das operações críticas
- [ ] T038: Testar isolamento entre tenants sem cross-access
