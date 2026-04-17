# Implementation Plan: Isolated Tenancy Architecture (Supabase)

**Branch**: `001-isolated-tenancy` | **Date**: 2026-04-17 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/001-multi-filial-tenant/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary
Refatoração da arquitetura multi-tenant para um modelo de **Isolamento Físico (Database-per-client)**. O sistema utiliza uma base central para gestão de assinantes e conexões dinâmicas para bases Supabase isoladas de cada cliente.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 12  
**Primary Dependencies**: `livewire/livewire`, `laravel/jetstream`, `laravel/fortify`, `supabase/postgrest-php` (CLI tools)  
**Storage**: PostgreSQL (Central) + Multiple PostgreSQL Instances (Tenants/Supabase)  
**Testing**: PHPUnit with SQLite (In-Memory for Isolation Tests)  
**Target Platform**: Web (SaaS)  
**Project Type**: Laravel Web Application  
**Performance Goals**: Tenant Resolution < 50ms, Scalability to 1000+ isolated databases.  
**Constraints**: Absolute physical isolation, no shared global scopes for core tenancy.  
**Scale/Scope**: Platform-wide management of client life cycle.

## ERP Modernization Context

**Modules**:
- Structural Registrations (Central)
- Tenant Provisioning (SaaS Automation)
- ERP Operations (Deliverables inside Tenant DB)

**Constitution Check**:
- **ALERTA**: Alteração fundamental do Princípio 1. Transição de isolamento lógico (coluna) para físico (instância).

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **GATE 1**: O isolamento é absoluto em nível de driver de banco? (PASS)
- **GATE 2**: Existe risco de cross-tenant leak no Banco Central? (PASS - apenas metadados)
- **GATE 3**: O provisionamento segue os Níveis de Modernização? (PASS - Nível 1: Infra)

## Project Structure

### Documentation (this feature)

```text
specs/001-multi-filial-tenant/
├── spec.md              # Feature specification
├── plan.md              # This file
├── research.md          # Architecture decisions (Isolated vs Column-based)
├── data-model.md        # Central and Tenant schemas
└── tasks.md             # Updated Roadmap (Database-per-client)
```

### Source Code (repository root)

```text
app/
├── Models/
│   ├── Central/        # Cliente, PlanoAssinatura, Fatura
│   └── Tenant/         # User, Post, WhiteLabelConfig
├── Http/
│   ├── Middleware/
│   │   ├── TenantConnectionMiddleware.php
│   │   └── PlatformAdminMiddleware.php
├── Livewire/
│   └── Admin/          # Super Admin Dashboard components
database/
├── migrations/
│   ├── central/        # Core platform schema
│   └── tenant/         # ERP specific schema per client
```

**Structure Decision**: Multi-database Laravel app with separate migration directories and dynamic connection switching.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| No branch_id filtering | Physical isolation (DB per client) | Column-based filtering is higher risk and less scalable for full SaaS isolation. |
| Hybrid multitenancy | Needs central control + isolated data | Single shared DB blocks client-specific bank repass and encryption needs. |
