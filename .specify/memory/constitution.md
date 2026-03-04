<!--
Sync Impact Report
- Version change: 1.3.1 → 1.4.0
- Modified principles:
  - I. Laravel 12 First → I. Laravel 12 First
  - II. Reactive UI via Livewire 4 + Filament → II. Reactive UI via Livewire 4 + Filament
  - III. Test-First Delivery (NON-NEGOTIABLE) → III. Test-First Delivery (NON-NEGOTIABLE)
  - IV. PostgreSQL Data Integrity → IV. PostgreSQL Data Integrity
  - V. Boost-Guided, Minimal Changes → V. Boost-Guided, Minimal Changes
  - VI. Production-Ready Integrations → VI. Production-Ready Integrations
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

# Laravel12JetstreamStarter Constitution

## Core Principles

### I. Laravel 12 First
All backend implementation MUST follow Laravel 12 conventions and native framework patterns.
Routing, middleware, exceptions, and console configuration MUST use `bootstrap/app.php` and
`routes/console.php` as applicable. Features MUST prefer Eloquent relationships, Form Requests,
policies, named routes, and framework commands over custom infrastructure.

Rationale: Reduces accidental complexity and keeps the codebase aligned with maintainable Laravel
standards.

### II. Reactive UI via Livewire 4 + Filament
Interactive UI MUST be built with Blade + Alpine + Livewire 4. The design system MUST follow the
Jetstream base template and its established UI patterns. Livewire components MUST follow
server-driven state, validation, authorization, and lifecycle hook conventions. Administrative and
data-heavy UIs MUST use Filament Forms and Filament Tables before custom alternatives are
introduced. Styling MUST use Tailwind CSS v4 utilities and existing project design tokens.

Rationale: Enforces a single, consistent UI architecture and prevents fragmented frontend patterns.

### III. Test-First Delivery (NON-NEGOTIABLE)
Every behavioral change MUST be covered by automated tests. Work MUST follow a red-green-refactor
cycle: write or update a failing test first, implement, then pass. Feature-level behavior MUST be
validated in PHPUnit Feature tests; unit tests MUST be used for isolated domain logic.

Rationale: Prevents regressions and keeps delivery confidence high while evolving the system.

### IV. PostgreSQL Data Integrity
Persistent data MUST target PostgreSQL. Schema changes MUST be shipped through Laravel migrations,
with explicit constraints, indexes, and foreign keys where applicable. Data access MUST prefer
Eloquent/query builder and MUST avoid bypassing model integrity rules.

Rationale: Protects data correctness and performance while keeping migrations auditable.

### V. Boost-Guided, Minimal Changes
For Laravel ecosystem decisions, implementation MUST consult Laravel Boost documentation search
before coding. Any question about framework or system behavior MUST use Laravel Boost as the
primary source of truth, treating it as the internal MCP for resolving doubts about the system and
framework. Changes MUST be minimal, scoped, and compatible with existing structure; dependency or
major architectural changes REQUIRE explicit approval.

Rationale: Ensures version-correct implementation choices and reduces risk from broad refactors.

### VI. Production-Ready Integrations
Background processing MUST use Laravel queues with Horizon for monitoring and operations. Outbound
HTTP calls MUST use Laravel's native HTTP client. Feature flags, integrations, and environment-
specific customization MUST be configured via environment variables and surfaced through config
files, never hard-coded or stored directly in source control.

Rationale: Standardizes integrations for reliability, visibility, and secure configuration.

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

**Version**: 1.4.0 | **Ratified**: 2026-02-19 | **Last Amended**: 2026-02-19
