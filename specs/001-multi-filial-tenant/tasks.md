# Tasks: Módulo Multi-Filial / Tenant

**Feature Branch**: `001-multi-filial-tenant`
**Spec File**: [spec.md](spec.md)

## Phase 1: Setup
- [ ] T001: Criar migration para tabela `filiais` (id, nome, cnpj, active).
- [ ] T002: Criar Model `Filial`.
- [ ] T003: Criar Seeder para a Filial primária/matriz.

## Phase 2: Foundational
- [ ] T004: Criar Trait `HasFilial` com suporte ao Global Scope.
- [ ] T005: Criar Middleware `SetFilialContext` para verificar login e determinar filial em uso na Session.
- [ ] T006: Criar Componente Livewire 4 para Seletor de Filial no Navbar.

## Phase 3: Testing
- [ ] T007: Escrever testes unitários garantindo que o escopo Global bloqueie invasão entre Tenants.
