# Tasks: Módulo 003 - Cadastros Estruturais

**Feature Branch**: `003-structural-registries`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T011, T017-T031
- **Business Domain Specialization**: T006-T023
- **Proactive Quality**: T005, T010-T011, T024-T031

## Phase 1: Database Migrations (Tenant)

- [x] T001: Criar migration `create_fabricantes_table`
- [x] T002: Criar migration `create_veiculos_table`
- [x] T003: Criar migration `create_baterias_table`
- [x] T004: Criar migration `create_aplicacoes_table` com unique constraint
- [x] T005: Criar migration `create_audit_logs_table`

## Phase 2: Models

- [x] T006: Criar Model `Fabricante`
- [x] T007: Criar Model `Veiculo` com cast para `atributos_dinamicos`
- [x] T008: Criar Model `Bateria` com cast para `atributos_dinamicos`
- [x] T009: Criar Model `Aplicacao`
- [x] T010: Criar Model `AuditLog`
- [x] T011: Criar Trait `Auditable` para disparar logs em `created`, `updated` e `deleted`

## Phase 3: CRUD Interfaces

- [x] T012: Criar Livewire component `FabricanteManager`
- [x] T013: Criar Livewire component `VeiculoManager`
- [x] T014: Criar Livewire component `BateriaManager`
- [x] T015: Implementar atributos dinâmicos com JSON schema
- [x] T016: Implementar validação de SKU único dentro do tenant

## Phase 4: Aplicações

- [x] T017: Criar Livewire component `ApplicationManager` como aba no veículo
- [x] T018: Implementar busca de baterias para adicionar aplicação
- [x] T019: Implementar validação de duplicidade em `aplicacoes`
- [x] T020: Implementar clonagem de aplicações entre veículos

## Phase 5: Buscas

- [x] T021: Implementar busca combinada de veículos por fabricante, modelo e ano
- [x] T022: Implementar busca reversa de veículos por bateria
- [x] T023: Implementar cache para busca offline em mobile

## Phase 6: Tests

- [x] T024: Testar CRUD de fabricante
- [x] T025: Testar CRUD de veículo
- [x] T026: Testar CRUD de bateria
- [x] T027: Testar criação de aplicação sem duplicidade
- [x] T028: Testar clonagem de aplicações
- [x] T029: Testar busca combinada
- [x] T030: Testar busca reversa de veículos por bateria
- [x] T031: Testar que ações são auditadas
- [x] T032: Testar isolamento entre tenants sem cross-access

## Phase 7: Edge Cases e Regras de Exclusão

- [x] T033: Implementar proteção para exclusão de fabricante com veículos vinculados
- [x] T034: Implementar proteção para exclusão de veículo com aplicações vinculadas
- [x] T035: Implementar proteção para exclusão de bateria com aplicações vinculadas
- [x] T036: Testar exclusão bloqueada ou soft delete de fabricante com vínculos
- [x] T037: Testar exclusão bloqueada ou confirmação controlada de veículo com aplicações
- [x] T038: Testar exclusão bloqueada ou confirmação controlada de bateria com aplicações
