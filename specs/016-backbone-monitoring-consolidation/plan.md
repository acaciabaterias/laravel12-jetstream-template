# Implementation Plan: Módulo 016 - Backbone Monitoring Consolidation

**Branch**: `016-backbone-monitoring-consolidation` | **Date**: 2026-05-13 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/016-backbone-monitoring-consolidation/spec.md`

## Summary

Consolidar a camada externa de monitoramento do ecossistema `010-015`, registrando targets, scrape health, regras de alerta e provisão versionada de dashboards Prometheus/Grafana. A implementação deve complementar o módulo `015`, e não substituí-lo, preservando o ERP como fonte de verdade de governança operacional.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 12  
**Primary Dependencies**: Eloquent, Livewire 4, PHPUnit, PostgreSQL, Redis, Prometheus, Grafana  
**Storage**: PostgreSQL central para catálogo de targets, snapshots de probe, regras de alerta, provisão de dashboards e evidências de readiness  
**Testing**: PHPUnit (`Feature`, `Unit`) via `php artisan test --compact`; validação de docs e artefatos com `git diff --check`  
**Target Platform**: ERP web multi-tenant com backoffice SaaS central e stack externo de observabilidade operando em containers Linux  
**Project Type**: Laravel web application com camada administrativa central e integração com observabilidade externa  
**Performance Goals**: readiness do monitoramento em < 3 interações; identificação explícita de scrape health; rollback versionado de dashboards e alertas  
**Constraints**: não introduzir dependências novas sem aprovação; não mover a governança operacional para fora do ERP; não mascarar falha de observabilidade externa como estado saudável  
**Scale/Scope**: backbone `010`, observability `015`, targets centrais, alertas versionados, dashboards externos e evidências de provisão

## ERP Modernization Context

**Modules**:
- Integration Backbone and Observability
- Platform Billing Control Plane
- Platform Payments and Reconciliation
- Platform Revenue Recovery
- Platform Commercial Analytics
- Production Observability Assurance

**Constitution Check**:
- O módulo complementa a resiliência operacional com monitoramento externo versionado.
- O plano inclui rollback de dashboards/alertas e evidência de readiness como parte do escopo principal.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Multi-tenancy and RBAC constraints are preserved. PASS
- Tests cover happy path and relevant failure path. PASS
- Stack constraints remain within approved technologies. PASS
- Documentation standards are applied proportionally to complexity. PASS
- Operational resilience controls are addressed when applicable. PASS
  - Falha de scrape precisa aparecer como degradação do monitoramento.
  - Provisionamento e rollback de dashboards/alertas exigem trilha auditável.

## Project Structure

### Documentation (this feature)

```text
specs/016-backbone-monitoring-consolidation/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── monitoring-events.md
│   └── monitoring-workflows.md
└── tasks.md
```

### Source Code (repository root)

```text
app/
├── Console/Commands/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Middleware/
├── Jobs/
├── Livewire/
│   └── Admin/
├── Models/
├── Policies/
├── Providers/
└── Services/
    ├── Contracts/
    ├── Integration/
    └── Operations/

config/
database/
├── factories/
└── migrations/
    └── central/

routes/
tests/
├── Feature/
└── Unit/
```

**Structure Decision**: Implementar a consolidação de monitoramento no monolito Laravel com catálogo central de targets, snapshots de probe, regras versionadas de alerta, registro de dashboards provisionados, inspeção administrativa reutilizável e integração com o backbone `010` para eventos materiais do próprio stack de observabilidade.

## Phase 0: Research

- Definir quais targets e exporters centrais devem fazer parte da malha mínima de monitoramento.
- Definir convenção de versionamento para dashboards e alertas por ambiente.
- Definir estratégia de readiness quando Prometheus ou Grafana estiverem indisponíveis.
- Definir guardrails para distinguir falha do fluxo monitorado e falha da observabilidade externa.

## Phase 1: Design

- Modelar targets monitorados, snapshots de probe, regras de alerta, provisão de dashboards e evidências de readiness.
- Definir contratos de eventos materiais da malha de monitoramento.
- Descrever painel administrativo central de monitoring readiness e inspeção JSON.
- Registrar quickstart para scrape health, alertas, provisão de dashboard e rollback de monitoramento.

## Phase 2: Task Planning Readiness

- O `tasks.md` deve separar:
  - fundação central de dados (`targets`, `probe_snapshots`, `alert_rules`, `dashboard_provisions`, `readiness_evidences`)
  - serviços de scrape health, versionamento e readiness
  - painel administrativo central e inspeção de monitoramento
  - testes de alerta, scrape, provisão e rollback
  - integração com backbone `010` e runbooks operacionais

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Persistência central do estado de monitoramento | Necessária para readiness auditável e comparação entre ambientes | Confiar apenas em Prometheus/Grafana perde rastreabilidade no ERP |
| Versionamento explícito de dashboards e alertas | Necessário para rollback verificável e coerência entre ambientes | Arquivos soltos ou painéis editados manualmente não são auditáveis |
