<!--
Sync Impact Report
- Version change: 1.4.0 → 1.5.0
- Modified principles:
  - I. Laravel 12 First → I. Business Domain Specialization
  - II. Reactive UI via Livewire 4 + Filament → II. Mobile-First Field Operations
  - III. Test-First Delivery (NON-NEGOTIABLE) → III. Automated Financial Microservices
  - IV. PostgreSQL Data Integrity → IV. Comprehensive Inventory & Reverse Logistics
  - V. Boost-Guided, Minimal Changes → V. Proactive Quality & Customer Service
  - VI. Production-Ready Integrations → VI. Integrated Fiscal Compliance
- Added sections:
  - None
- Removed sections:
  - None
- Templates requiring updates:
  - ✅ updated: .specify/templates/plan-template.md
  - ✅ updated: .specify/templates/spec-template.md
  - ⚠ pending: .specify/templates/commands/*.md (directory not present in repository)
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

**Version**: 1.5.0 | **Ratified**: 2026-02-19 | **Last Amended**: 2026-04-11

# Constituição do Projeto: ERP Baterias

## Princípios de Arquitetura
1. **Multi-tenancy Absoluto:** Todo e qualquer dado deve ser filtrado por `branch_id` (Filial). O isolamento de dados é a prioridade número 1.
2. **Precedência Hierárquica:** O desenvolvimento deve seguir a ordem de Níveis (1 a 4). Nenhuma funcionalidade de nível superior pode ser implementada sem que a sua dependência no nível inferior esteja estável.
3. **RBAC (Role-Based Access Control):** O acesso a funcionalidades e dados é estritamente controlado por perfis de utilizador.

## Padrão de Documentação (SDD)
Todas as especificações devem seguir obrigatoriamente a estrutura:
- **Contexto e Dependências:** O que é e o que precisa de existir antes.
- **User Scenarios (Given-When-Then):** Comportamento esperado.
- **Edge Cases:** Tratamento de erros e exceções.
- **Functional Requirements (FR):** Regras técnicas numeradas.
- **Success Criteria (SC):** Como medir se a funcionalidade funciona.
