# Tasks: Módulo 002 - RBAC (Database-per-Client)

**Feature Branch**: `002-users-permissions-rbac`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T007, T010, T013, T018-T023
- **RBAC**: T005-T012, T014-T019
- **Auditoria de Acesso**: T004, T007, T013, T021-T023

## Phase 1: Database Migrations (Tenant)

- [x] T001: Criar migration `create_users_table` no tenant sem `filial_id`
- [x] T002: Criar migration `create_permissoes_table` no tenant
- [x] T003: Criar migration `create_papel_permissao_table` no tenant
- [x] T004: Criar migration `create_audit_logs_acesso_table` no tenant

## Phase 2: Models and Seeders

- [x] T005: Criar Model `User` no tenant com papéis como enum
- [x] T006: Criar Model `Permissao`
- [x] T007: Criar Model `AuditLogAcesso`
- [x] T008: Criar Seeder `PermissoesSeeder` com permissões padrão
- [x] T009: Criar Seeder `PapelPermissaoSeeder` associando permissões aos papéis

## Phase 3: Authentication & Authorization

- [x] T010: Configurar Fortify para usar a conexão tenant
- [x] T011: Criar `UserPolicy` com regras baseadas em papel
- [x] T012: Registrar Gates no `AuthServiceProvider`
- [x] T013: Implementar registro de auditoria de acesso para login com sucesso e falha

## Phase 4: User Management UI

- [x] T014: Criar Livewire component `UserManager` para listar usuários
- [x] T015: Criar Livewire component `UserForm` para criar e editar usuários
- [x] T016: Implementar validação de email único dentro do tenant
- [x] T017: Implementar ativação e desativação de usuários

## Phase 5: Tests

- [x] T018: Testar que `dono` pode criar `vendedor`
- [x] T019: Testar que `vendedor` não pode criar usuários
- [x] T020: Testar que Super Admin acessa tenant via seletor de contexto
- [x] T021: Testar que usuário inativo não autentica
- [x] T022: Testar que tentativa de acesso sem permissão retorna HTTP 403
- [x] T023: Testar que auditoria registra IP, User Agent e timestamp

## Phase 6: Super Admin (Central Database)

- [x] T024: Criar migration `create_usuarios_plataforma_table` no banco central
- [x] T025: Criar Model `UsuarioPlataforma` na conexão central
- [x] T026: Criar Seeder `SuperAdminSeeder`
- [x] T027: Criar middleware para verificar `super_admin` em rotas administrativas da plataforma
