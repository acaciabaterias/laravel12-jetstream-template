# Tasks: Módulo 003 - Cadastros Estruturais

**Feature Branch**: `003-structural-registries`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T011, T017-T031
- **Business Domain Specialization**: T006-T023
- **Proactive Quality**: T005, T010-T011, T024-T031

## Phase 1: Database Migrations (Tenant)

- [ ] T001: Criar migration `create_fabricantes_table`
- [ ] T002: Criar migration `create_veiculos_table`
- [ ] T003: Criar migration `create_baterias_table`
- [ ] T004: Criar migration `create_aplicacoes_table` com unique constraint
- [ ] T005: Criar migration `create_audit_logs_table`

## Phase 2: Models

- [ ] T006: Criar Model `Fabricante`
- [ ] T007: Criar Model `Veiculo` com cast para `atributos_dinamicos`
- [ ] T008: Criar Model `Bateria` com cast para `atributos_dinamicos`
- [ ] T009: Criar Model `Aplicacao`
- [ ] T010: Criar Model `AuditLog`
- [ ] T011: Criar Trait `Auditable` para disparar logs em `created`, `updated` e `deleted`

## Phase 3: CRUD Interfaces

- [ ] T012: Criar Livewire component `FabricanteManager`
- [ ] T013: Criar Livewire component `VeiculoManager`
- [ ] T014: Criar Livewire component `BateriaManager`
- [ ] T015: Implementar atributos dinâmicos com JSON schema
- [ ] T016: Implementar validação de SKU único dentro do tenant

## Phase 4: Aplicações

- [ ] T017: Criar Livewire component `ApplicationManager` como aba no veículo
- [ ] T018: Implementar busca de baterias para adicionar aplicação
- [ ] T019: Implementar validação de duplicidade em `aplicacoes`
- [ ] T020: Implementar clonagem de aplicações entre veículos

## Phase 5: Buscas

- [ ] T021: Implementar busca combinada de veículos por fabricante, modelo e ano
- [ ] T022: Implementar busca reversa de veículos por bateria
- [ ] T023: Implementar cache para busca offline em mobile

## Phase 6: Tests

- [ ] T024: Testar CRUD de fabricante
- [ ] T025: Testar CRUD de veículo
- [ ] T026: Testar CRUD de bateria
- [ ] T027: Testar criação de aplicação sem duplicidade
- [ ] T028: Testar clonagem de aplicações
- [ ] T029: Testar busca combinada
- [ ] T030: Testar busca reversa de veículos por bateria
- [ ] T031: Testar que ações são auditadas
- [ ] T032: Testar isolamento entre tenants sem cross-access

## Phase 7: Edge Cases e Regras de Exclusão

- [ ] T033: Implementar proteção para exclusão de fabricante com veículos vinculados
- [ ] T034: Implementar proteção para exclusão de veículo com aplicações vinculadas
- [ ] T035: Implementar proteção para exclusão de bateria com aplicações vinculadas
- [ ] T036: Testar exclusão bloqueada ou soft delete de fabricante com vínculos
- [ ] T037: Testar exclusão bloqueada ou confirmação controlada de veículo com aplicações
- [ ] T038: Testar exclusão bloqueada ou confirmação controlada de bateria com aplicações
