# Tasks: Módulo Multi-Filial / Tenant

**Feature Branch**: `001-multi-filial-tenant`
**Spec File**: [spec.md](spec.md)

## Phase 1: Setup
- [x] T001: Criar migration para tabela `filiais` (id, nome, cnpj, active).
- [x] T002: Criar Model `Filial`.
- [x] T003: Criar Seeder para a Filial primária/matriz.

## Phase 2: Foundational
- [x] T004: Criar Trait `HasFilial` com suporte ao Global Scope.
- [x] T005: Criar Middleware `SetFilialContext` para verificar login e determinar filial em uso na Session.
- [x] T006: Criar Componente Livewire 4 para Seletor de Filial no Navbar.

## Phase 3: Testing
- [x] T007: Escrever testes unitários garantindo que o escopo Global bloqueie invasão entre Tenants.

## Phase 4: SaaS & White Label (Refactor)
- [x] T008: Adicionar campos SaaS na migration de filiais
- [x] T009: Criar migration white_label_configs
- [x] T010: Criar migration planos_assinatura
- [x] T011: Criar migration assinaturas
- [x] T012: Criar migration faturas
- [x] T013: Criar Model WhiteLabelConfig
- [x] T014: Criar Model PlanoAssinatura
- [x] T015: Criar Model Assinatura
- [x] T016: Criar Model Fatura
- [x] T017: Adicionar métodos hasActiveSubscription() e canAccessFeature() no Model Filial
- [x] T018: Criar Middleware TenantResolver
- [x] T019: Atualizar layout app.blade.php com suporte white label
- [x] T020: Criar Seeder PlanoAssinaturaSeeder
- [x] T021: Testar resolução de subdomínio
- [x] T022: Testar bloqueio de assinatura expirada
- [x] T023: Testar customização white label
