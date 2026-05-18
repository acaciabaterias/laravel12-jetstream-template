# Implementation Plan: Módulo 020 - Advanced Revenue Recovery Automation

**Branch**: `020-advanced-revenue-recovery-automation` | **Date**: 2026-05-16 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/020-advanced-revenue-recovery-automation/spec.md`

## Summary

Expandir o módulo `013` com automação avançada de cobrança, incluindo versionamento de políticas, jornadas adaptativas com fallback e supressão, experimentos controlados, detecção de violações e rollback seguro de estratégia quando a performance degradar.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 12  
**Primary Dependencies**: Eloquent, Livewire 4, Laravel Queue, Laravel Notifications, PHPUnit, PostgreSQL  
**Storage**: PostgreSQL central para versões de política, jornadas automatizadas, dispatches, experimentos e violações; Redis para filas, retries e agendamento operacional  
**Testing**: PHPUnit (`Feature`, `Unit`) via `php artisan test --compact`  
**Target Platform**: ERP web multi-tenant com painel central de plataforma e workflows comerciais automatizados  
**Project Type**: Laravel web application com control plane central e automação operacional  
**Performance Goals**: definir próxima ação automatizada elegível em < 5 min; refletir degradação material ou violação em < 1 min no painel; executar rollback governado em até 3 interações  
**Constraints**: não mover workflow comercial para bancos tenant; não disparar contatos duplicados por janela; não publicar política sem guardrails; não perder vínculo entre caso, variante experimental e rollback; não introduzir dependências novas sem aprovação  
**Scale/Scope**: carteira SaaS central com múltiplos segmentos, variantes controladas de automação, dispatch multicanal e governança de rollback

## ERP Modernization Context

**Modules**:
- Users and Profiles / RBAC
- Integration Backbone and Observability
- Platform Billing Control Plane
- Platform Payments and Reconciliation
- Platform Revenue Recovery
- Executive Reporting Hub

**Constitution Check**:
- O módulo amplia automação comercial sobre receita recorrente sem romper isolamento multi-tenant.
- O plano exige rollback auditável, replay controlado e evidência de governança para políticas automatizadas.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Multi-tenancy and RBAC constraints are preserved. PASS
- Tests cover happy path and relevant failure path. PASS
- Stack constraints remain within approved technologies. PASS
- Documentation standards are applied proportionally to complexity. PASS
- Operational resilience controls are addressed when applicable. PASS
  - Estratégias degradadas precisam de rollback seguro e comparável.
  - Dispatches automáticos exigem deduplicação, supressão e replay auditável.

## Project Structure

### Documentation (this feature)

```text
specs/020-advanced-revenue-recovery-automation/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── automation-events.md
│   └── automation-workflows.md
└── tasks.md
```

### Source Code (repository root)

```text
app/
├── Console/Commands/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Jobs/
├── Livewire/
│   └── Admin/
├── Models/
├── Policies/
├── Providers/
└── Services/
    ├── Billing/
    ├── Contracts/
    └── Integration/

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

**Structure Decision**: Implementar a automação avançada no monolito Laravel, reaproveitando `CasoRecuperacaoReceita` e `AcaoRecuperacaoReceita` do módulo `013` como origem operacional, adicionando persistência central para políticas versionadas, jornadas, experimentos, dispatches e violações, além de serviços em `app/Services/Billing/` para orquestração, governança e rollback.

## Phase 0: Research

- Definir a fronteira entre política automatizada ativa, experimento controlado e holdout.
- Definir estratégia de fallback, cooldown e supressão para evitar excesso de contato.
- Definir critérios objetivos para violação material e degradação de performance.
- Definir rollback seguro de política sem reescrever histórico já tratado.

## Phase 1: Design

- Modelar versões de política, jornadas automatizadas, dispatches, experimentos e violações.
- Definir contratos dos eventos materiais de publicação, dispatch, violação e rollback.
- Descrever painel central de automação, inspeção de performance e operações de rollback.
- Registrar quickstart para publicação controlada, dispatch automatizado e reversão auditável.

## Phase 2: Task Planning Readiness

- O `tasks.md` deve separar:
  - fundação central de dados (`recovery_automation_policy_versions`, `recovery_automation_journeys`, `recovery_automation_dispatches`, `recovery_automation_experiments`, `recovery_automation_violations`)
  - serviços de orquestração, fallback, experimento, guardrail e rollback
  - painel administrativo central e inspeção reutilizável da automação
  - testes de dispatch adaptativo, holdout, violação e rollback
  - integração com backbone `010`, recovery `013`, executive reporting `019` e runbooks operacionais

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Versionamento explícito de política automatizada | Necessário para rollback, comparação e governança de estratégia | Alterar regras ativas em linha elimina rastreabilidade e reversão segura |
| Persistência de experimentos e violações | Necessária para validar variantes e bloquear automações degradadas | Medir apenas recuperação agregada não explica qual estratégia tratou cada caso |
