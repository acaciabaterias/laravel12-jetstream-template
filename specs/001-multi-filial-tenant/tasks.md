# Tasks: 001-multi-filial-tenant (Isolated Tenancy)

**Branch**: `001-multi-filial-tenant`
**Input**: Design documents from `/specs/001-multi-filial-tenant/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, quickstart.md

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- Central database migrations: `database/migrations/central/`
- Tenant database migrations: `database/migrations/tenant/`
- Central models: `app/Models/` (connected to `central`)
- Tenant models: `app/Models/` (connected to `tenant`)

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [x] T001 Update `config/database.php` to define `central` and dynamic `tenant` connections.
- [x] T002 Organize database migration directories (`central` vs `tenant`).
- [x] T003 Set `sqlite` dynamically for in-memory parallel testing in `phpunit.xml`.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

- [x] T004 Create `clientes` migration in `database/migrations/central/`
- [x] T005 Create `planos` and `assinaturas` migrations in `database/migrations/central/`
- [x] T006 Create `faturas` migration in `database/migrations/central/`
- [x] T007 [P] Create `Cliente` model mapping to `central` connection in `app/Models/Cliente.php`
- [x] T008 [P] Create `PlanoAssinatura` model mapping to `central` connection in `app/Models/PlanoAssinatura.php`
- [x] T009 [P] Create `Assinatura` model mapping to `central` connection in `app/Models/Assinatura.php`
- [x] T010 [P] Create `Fatura` model mapping to `central` connection in `app/Models/Fatura.php`

**Checkpoint**: Foundation ready - user story implementation can now begin.

---

## Phase 3: User Story 1 - Acesso via Subdomínio (Priority: P1) 🎯 MVP

**Goal**: Middleware processa a requisição via `{subdominio}.erp.com`, valida no Banco Central e direciona as queries para o banco específico do cliente no Supabase.

**Independent Test**: The latency for subdomain resolution should be < 50ms and user data strictly isolated.

### Tests for User Story 1

- [x] T011 [P] [US1] Integration test for tenant resolution and database switching in `tests/Feature/TenantResolutionTest.php`
- [x] T012 [P] [US1] Performance test verifying tenant resolution latency (<50ms) in tests.

### Implementation for User Story 1

- [x] T013 [US1] Implement `TenantConnectionMiddleware` in `app/Http/Middleware/TenantConnectionMiddleware.php`
- [x] T014 [US1] Register `TenantConnectionMiddleware` globally for web routes in `bootstrap/app.php`
- [x] T015 [P] [US1] Modify `app/Models/User.php` to use `tenant` connection and remove `filial_id`.
- [x] T016 [P] [US1] Modify `app/Models/Post.php` to use `tenant` connection and remove `filial_id`.
- [x] T017 [US1] Remove legacy `FilialSelector` from `resources/views/navigation-menu.blade.php`.

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - Provisionamento de Novo Tenant (Priority: P2)

**Goal**: Admins na Plataforma disparam a automação que cria projetos fisicamente no Supabase, cadastra no Banco Central e roda as migrações.

**Independent Test**: Admin dashboard loads correctly, provisioning flow runs without error and the new tenant connects successfully.

### Tests for User Story 2

- [x] T018 [P] [US2] Integration test for tenant provisioning CLI command.

### Implementation for User Story 2

- [x] T019 [US2] Create and configure `PlatformAdminMiddleware` in `app/Http/Middleware/PlatformAdminMiddleware.php`
- [x] T020 [US2] Create `platform_users` auth provider and guard in `config/auth.php`
- [x] T021 [US2] Register admin routes under `admin.{domain}` in `bootstrap/app.php` and create `routes/admin.php`
- [x] T022 [US2] Create `UsuarioPlataforma` central model and seeder in `database/seeders/DatabaseSeeder.php`
- [x] T023 [US2] Create premium Volt Layout for Admin platform in `resources/views/layouts/admin.blade.php`
- [x] T024 [P] [US2] Implement Admin Home Dashboard Volt component in `resources/views/pages/admin/dashboard.blade.php`
- [x] T025 [P] [US2] Implement Clients Management Volt component in `resources/views/pages/admin/clientes/index.blade.php`
- [x] T026 [P] [US2] Implement Tenant Provisioning form Volt component in `resources/views/pages/admin/clientes/create.blade.php`
- [x] T027 [US2] Develop `tenant:create` artisan command integrating Supabase API in `app/Console/Commands/`

**Checkpoint**: Admin platform successfully registers and manages independent Supabase environments.

---

## Phase 5: User Story 3 - Customização White Label (Priority: P3)

**Goal**: A interface do ERP reflete as cores, logo e branding configurados no White Label próprio.

**Independent Test**: ERP dashboard injected css variables match the values retrieved from the tenant's db.

### Tests for User Story 3

- [x] T028 [P] [US3] Unit tests verifying branding application from tenant settings.

### Implementation for User Story 3

- [x] T029 [P] [US3] Create `white_label_configs` migration in `database/migrations/tenant/` (without filial_id).
- [x] T030 [P] [US3] Modify `WhiteLabelConfig` model to map to `tenant` connection.
- [x] T031 [US3] Update Blade layout `resources/views/layouts/app.blade.php` to fetch branding from active tenant rather than relation.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T032 [P] Remove `app/Traits/HasFilial.php`
- [x] T033 [P] Remove `app/Models/Scopes/MultiTenantScope.php`
- [x] T034 [P] Remove `app/Http/Middleware/SetFilialContext.php`
- [x] T035 [P] Remove `app/Http/Middleware/TenantResolver.php`
- [x] T036 Code cleanup and refactoring (Laravel Pint)
- [x] T037 Security hardening regarding Supabase connection string storage

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Completed during architecture transition.
- **Foundational (Phase 2)**: Completed during data model transition.
- **User Stories (Phase 3+)**: US1, US2, and US3 are largely independently implemented. All depend on Foundational phase completion.
- **Polish (Final Phase)**: Removing legacy traits and scopes follows the switch to physical isolation.

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Central DB connects correctly and defines Tenants.
2. The middleware resolves subdomains.
3. Eloquent models point to the respective physical DB cleanly.

### Current Status
Most foundational logic, User Story 1, and User Story 2 have been completed. Next logical focus is ensuring proper CLI interaction for Supabase integration (`tenant:create`) if not yet functionally built out, and expanding test coverages.
