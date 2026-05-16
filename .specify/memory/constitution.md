<!--
Sync Impact Report
- Version change: 1.5.0 → 1.6.0
- Modified principles:
  - VII. Mandatory Documentation & Algorithmic Commenting → VII. Mandatory Documentation Standards
- Added sections:
  - VIII. Operational Resilience & Disaster Recovery
- Removed sections:
  - Legacy duplicate section: "Constituição do Projeto: ERP Baterias"
- Templates requiring updates:
  - ✅ updated: .specify/templates/plan-template.md
  - ✅ updated: .specify/templates/spec-template.md
  - ✅ updated: .specify/templates/tasks-template.md
- Follow-up TODOs:
  - None
-->

# BateriaExpert ERP Constitution

## Core Principles

### I. Business Domain Specialization
The ERP MUST be a specialist system for automotive battery resale management, deeply understanding and implementing specific business rules such as scrap weight control and reverse logistics. The system MUST replace generic solutions by embedding a validated 27-year workflow.

Rationale: Ensures the software directly addresses the unique needs of the automotive battery resale market, reducing manual effort and complexity.

### II. Mobile-First Field Operations
Deliverers MUST interact with the system via a mobile-centric interface, enabling real-time access to routes, on-site adjustments of scrap weight, and direct recording of payment receipts. This MUST ensure seamless integration between field and in-store operations.

Rationale: Enhances operational efficiency, improves data accuracy by capturing information at the source, and bridges the gap between external and internal workflows.

### III. Automated Financial Microservices
Financial processes MUST leverage microservices for automated bank reconciliation via API, streamlined boleto issuance, and automatic payment baixa, thereby minimizing manual data entry and ensuring financial accuracy.

Rationale: Automates critical financial tasks, reduces human error, and provides real-time financial visibility and control.

### IV. Comprehensive Inventory & Reverse Logistics
The system MUST provide robust inventory and reverse logistics management, including automatic stock entry via XML import from suppliers, precise "Scrap Account" management for clients and suppliers, and proactive monitoring of battery "Shelf Life" to prevent charge loss.

Rationale: Optimizes inventory levels, ensures compliance with reverse logistics requirements, and minimizes financial losses due to product degradation.

### V. Proactive Quality & Customer Service
The system MUST manage product guarantees with full traceability to original sales and clients, facilitate battery loans, and automate customer notifications via WhatsApp for status updates. It MUST also generate detailed quality reports by brand, model, and time-to-claim to dynamically establish product return indices.

Rationale: Improves customer satisfaction, enhances after-sales support, and provides critical data for product quality control and supplier negotiation.

### VI. Integrated Fiscal Compliance
The system MUST communicate with dedicated microservices for the issuance of Fiscal Coupons (PDV) and NF-e. Users MUST be able to consult, print, correct, cancel, and generate accounting reports for these documents directly through the ERP interface.

Rationale: Ensures full compliance with fiscal regulations, simplifies tax reporting, and provides users with direct control over their fiscal documentation.

### VII. Mandatory Documentation Standards

All PHP files within the '/app/' directory (including Classes, Controllers, Models, Actions, and Commands) MUST be thoroughly documented.

- **PHPDoc**: Every method MUST have a complete PHPDoc block including parameter and return types.
- **Algorithmic clarity**: Complex flows MUST include concise explanatory comments. Trivial logic MUST NOT be over-commented.
- **Consistency**: Every created or modified file MUST adhere to this documentation standard before being considered complete.

### VIII. Operational Resilience & Disaster Recovery

The platform MUST enforce operational continuity with verifiable backup and recovery controls for core infrastructure, including the active VM topology.

- **Backup coverage**: Central database, tenant databases, and critical application configuration MUST be included in recurring backups.
- **Restore validation**: At least one restore rehearsal per environment MUST be executed on a defined cadence and recorded with timestamp, operator, and outcome.
- **Retention policy**: Retention windows (daily/weekly/monthly) MUST be defined and enforced in automation.
- **Deploy gate**: Production deployments MUST require evidence of a recent valid backup and documented rollback path.
- **Evidence trail**: Backup and restore results MUST be auditable through runbooks, logs, or checklist artifacts.

Rationale: Stable operations require recovery guarantees, not only successful deployments. This principle reduces downtime and data loss risk.

## Princípios de Arquitetura

### Multi-Tenancy (Filial)
- Todo registro no banco de dados DEVE conter `filial_id` como chave estrangeira
- Isolamento de dados entre filiais é obrigatório
- Usuários só podem acessar dados da(s) sua(s) filial(is)

### RBAC (Perfis)
- O sistema deve implementar controle de acesso baseado em papéis (Roles)
- Perfis mínimos: Dono, Gestor, Vendedor, Técnico, Estoquista
- Auditoria de acesso (IP, dispositivo, timestamp) é obrigatória

### Ordem de Implementação
Os módulos DEVEM ser implementados na seguinte ordem:
1. Filial/Tenant
2. Cadastros Estruturais
3. Usuários e Perfis
4. Estoque
5. Vendas e Assistência
6. Empréstimo de Ativos
7. Conciliação Bancária

## Technology Stack Constraints

The canonical application stack is:
- Laravel 12
- Blade + Alpine.js
- Livewire 4
- Filament Forms + Filament Tables
- Tailwind CSS 4
- PostgreSQL
- Laravel Jetstream
- Laravel Boost + Laravel AI
- PHPUnit
- Laravel Horizon
- Laravel HTTP Client
- Laravel Pennant
- Dedicated microservices for financial and fiscal automation.

Any proposal that introduces an additional framework for a capability already covered by this stack
MUST include written justification and explicit approval before implementation.

## Development Workflow & Quality Gates

1. Specification artifacts (`spec.md`, `plan.md`, `tasks.md`) MUST explicitly map to constitution
   principles.
2. Constitution check gates in planning MUST pass before implementation begins and MUST be
   re-validated after design.
3. Pull requests MUST document: scope, tests executed, migration impact, and principle compliance.
4. New endpoints, workflows, or UI paths MUST include test coverage for both happy paths and
   relevant failure paths.
5. Formatting and static quality tooling configured by the repository MUST run on changed files
   before finalization.
6. Migration files MUST be named in English and follow Laravel's migration naming conventions.
7. When creating a new screen or interface, clarifying questions MUST ask the user for their
  desired UI direction before implementation.
8. Plans and go-live checklists MUST include explicit backup and restore evidence when infrastructure or data-impacting changes are involved.

## Governance

This constitution supersedes local workflow preferences when conflict exists.

Amendment Process:
- Propose changes in a documented update that includes rationale, affected principles, and
  migration/transition impact.
- Obtain maintainer approval before merging amendments.
- Record all amendments with a semantic version update.

Versioning Policy:
- MAJOR: Backward-incompatible governance changes, removed principles, or principle redefinitions.
- MINOR: New principle/section added or materially expanded governance guidance.
- PATCH: Clarifications, wording improvements, and non-semantic refinements.

Compliance Review Expectations:
- Every implementation plan MUST include a Constitution Check.
- Every task list MUST include explicit testing tasks.
- Every pull request review MUST verify constitutional compliance prior to approval.

**Version**: 1.6.0 | **Ratified**: 2026-02-19 | **Last Amended**: 2026-05-05
