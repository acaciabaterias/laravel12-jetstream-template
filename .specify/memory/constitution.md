<!--
Sync Impact Report
- Version change: 1.5.0 → 2.0.0
- Modified principles:
  - Multi-Tenancy (Filial) → Multi-Tenancy Isolado (Database-per-Client)
  - Multi-tenancy Absoluto → Removed (merged into Multi-Tenancy Isolado)
- Added sections:
  - None
- Removed sections:
  - Duplicate legacy "Constituição do Projeto" block.
- Templates requiring updates:
  - ✅ updated: .specify/templates/plan-template.md
  - ✅ updated: .specify/templates/spec-template.md
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

## Princípios de Arquitetura

### Multi-Tenancy Isolado (Database-per-Client)
- A infraestrutura adota isolamento FÍSICO a nível de banco de dados suportado por Supabase/PostgreSQL. O uso de `filial_id` ou `branch_id` como modelo de separação lógica (via Global Scopes) está estritamente **obsoleto** e NÃO DEVE ser utilizado.
- Operações do core do ERP e modelos de Tenant DEVEM obrigatoriamente operar sobre a conexão de runtime resolvida pelo App\Http\Middleware\TenantConnectionMiddleware.
- Metadados universais e administrativos (como Gestão de Lojistas, Assinaturas e Pagamentos Base) DEVEM repousar estritamente na conexão genérica `central`.

### RBAC (Perfis)
- O sistema deve implementar controle de acesso baseado em papéis (Roles)
- Perfis mínimos: Dono, Gestor, Vendedor, Técnico, Estoquista
- Auditoria de acesso (IP, dispositivo, timestamp) é obrigatória

### Ordem de Implementação
Os módulos DEVEM ser implementados na seguinte ordem:
1. Multi-Tenancy Isolado (Supabase/DB-per-client)
2. Autenticação e Perfis Tenant-aware
3. Cadastros Estruturais Isolados
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

**Version**: 2.0.0 | **Ratified**: 2026-02-19 | **Last Amended**: 2026-04-17

## Padrão de Documentação Secundário (Legacy SDD)
Todas as especificações de features menores DEVEM continuar seguindo opcionalmente a estrutura:
- **Contexto e Dependências:** O que é e o que precisa de existir antes.
- **User Scenarios (Given-When-Then):** Comportamento esperado.
- **Edge Cases:** Tratamento de erros e exceções.
- **Functional Requirements (FR):** Regras técnicas numeradas.
- **Success Criteria (SC):** Como medir se a funcionalidade funciona.
