# Tasks: Módulo de Usuários e Perfis (RBAC)

**Feature Branch**: `002-users-permissions-rbac`
**Spec File**: [spec.md](spec.md)

## Phase 1: Database & Migrations

- [x] T001: Criar migration `add_filial_id_to_users_table` com `filial_id` BIGINT UNSIGNED NULL
- [x] T002: Criar migration `add_papel_to_users_table` com `papel` ENUM('super_admin','dono','gestor','vendedor','tecnico','estoquista')
- [x] T003: Criar migration `add_ativo_to_users_table` com `ativo` BOOLEAN DEFAULT TRUE
- [x] T004: Adicionar índices para `filial_id` e `papel` na tabela users
- [x] T005: Garantir UNIQUE constraint no email da tabela users

## Phase 2: Models & Traits

- [x] T006: Atualizar Model User com `fillable` para novos campos
- [x] T007: Adicionar relacionamento `belongsTo(Filial::class)` no Model User
- [x] T008: Adicionar método `isSuperAdmin()` no Model User
- [x] T009: Adicionar método `hasRole($papel)` no Model User

## Phase 3: Middleware

- [x] T010: Criar middleware `FilialIsolation`
- [x] T011: Registrar middleware `filial.isolation` no `bootstrap/app.php`
- [x] T012: Aplicar middleware às rotas protegidas (web e api)

## Phase 4: Seeders

- [x] T013: Criar seeder `SuperAdminSeeder`
- [x] T014: Registrar seeder no `DatabaseSeeder.php`
- [x] T015: Adicionar variáveis `SUPER_ADMIN_EMAIL` e `SUPER_ADMIN_PASSWORD` no `.env.example`

## Phase 5: Tests

- [x] T016: Testar que super_admin acessa todos os CNPJs
- [x] T017: Testar que usuário comum NÃO acessa outro CNPJ (HTTP 403)
- [x] T018: Testar que usuário sem filial_id (não super_admin) é bloqueado
- [x] T019: Testar que middleware não interfere em rotas públicas
- [x] T020: Testar que papéis têm as permissões corretas (CRUD de recursos)

## Phase 6: Integration

- [x] T021: Adicionar seletor de CNPJ no dashboard do super_admin
- [x] T022: Implementar listagem de usuários por CNPJ (apenas dono/gestor)
- [x] T023: Implementar criação de usuários com `filial_id` automático (contexto atual)
