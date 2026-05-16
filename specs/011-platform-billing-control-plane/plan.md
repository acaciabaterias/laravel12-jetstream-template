# Implementation Plan: Módulo 011 - Platform Billing Control Plane

**Branch**: `011-platform-billing-control-plane` | **Date**: 2026-05-07 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/011-platform-billing-control-plane/spec.md`

## Summary

Formalizar a camada central comercial do SaaS para controlar planos, assinaturas, faturas, grace period, bloqueio, desbloqueio e indicadores de saúde da base de assinantes. A implementação deve permanecer no banco central, integrar-se ao backbone `010` por eventos operacionais e reutilizar o RBAC e a trilha de auditoria já existentes.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 12
**Primary Dependencies**: Eloquent, Livewire 4, Laravel Queue, Laravel HTTP Client, PostgreSQL, PHPUnit
**Storage**: PostgreSQL central para planos, assinaturas, faturas SaaS, políticas e trilha operacional; Redis para filas e processamento assíncrono auxiliar quando necessário
**Testing**: PHPUnit (`Feature`, `Unit`) executado via `php artisan test --compact`
**Target Platform**: ERP web multi-tenant em containers Linux com painel central de plataforma
**Project Type**: Laravel web application com backoffice SaaS central e integrações assíncronas
**Performance Goals**: bloqueio elegível identificado em < 1 min; reativação operacional em < 3 min; painel central respondendo em < 2 s para filtros usuais
**Constraints**: não romper isolamento tenant; não duplicar lógica financeira dos módulos operacionais; manter trilha auditável de toda mudança comercial crítica; não introduzir dependências novas sem aprovação
**Scale/Scope**: catálogo central de planos, múltiplos assinantes, ciclo de cobrança recorrente, eventos comerciais e painel super admin

## ERP Modernization Context

**Modules**:
- Multi-Tenancy Isolado
- Users and Profiles / RBAC
- Intelligent Financial Module
- Integration Backbone and Observability

**Constitution Check**:
- O módulo impacta estado comercial central e continuidade de acesso dos tenants.
- O plano inclui backup/restore/rollback para planos, assinaturas, cobranças SaaS e estados de bloqueio.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Multi-tenancy and RBAC constraints are preserved. PASS
- Tests cover happy path and relevant failure path. PASS
- Stack constraints remain within approved technologies. PASS
- Documentation standards are applied proportionally to complexity. PASS
- Operational resilience controls are addressed when applicable. PASS
  - Recent valid backup evidence already documented in operational artifacts.
  - Restore rehearsal evidence already documented for the current environment.
  - Rollback path must be expanded to include commercial state reversal for this module.

## Project Structure

### Documentation (this feature)

```text
specs/011-platform-billing-control-plane/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── billing-events.md
│   └── admin-workflows.md
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
├── Notifications/
├── Policies/
├── Providers/
└── Services/
    ├── Billing/
    └── Contracts/

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

**Structure Decision**: Implementar o billing control plane no próprio monolito Laravel usando banco central, Livewire para o painel administrativo, serviços dedicados em `app/Services/Billing/` e eventos publicados no backbone `010` apenas quando houver mudança operacional relevante.

## Phase 0: Research

- Consolidar o modelo comercial central entre plano, assinatura, cobrança SaaS e política de inadimplência.
- Definir a fronteira entre módulo `011` e módulo `008`, evitando duplicidade de lógica financeira.
- Definir gatilhos de bloqueio, desbloqueio, grace period e cancelamento com trilha auditável.
- Definir o conjunto mínimo de eventos comerciais que devem ser publicados no backbone `010`.

## Phase 1: Design

- Modelar entidades centrais de plano, assinatura, fatura, política e evento operacional.
- Definir contratos de eventos comerciais e fluxo administrativo do super admin.
- Descrever validação operacional para bloqueio, reativação, troca de plano e encerramento.
- Registrar quickstart para validação local e operacional do módulo central.

## Phase 2: Task Planning Readiness

- O `tasks.md` deve separar:
  - fundação central de dados (`planos`, `assinaturas`, `faturas_saas`, `politicas`, `eventos`)
  - serviços de governança comercial e jobs assíncronos
  - painel administrativo central e filtros operacionais
  - testes de regra comercial, bloqueio, desbloqueio e visão consolidada
  - integração com backbone `010` e runbooks operacionais

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Estado comercial central persistido | Necessário para bloquear, reativar e auditar assinantes | Flags soltas no cadastro do cliente não sustentam histórico nem política configurável |
| Eventos comerciais integrados ao backbone | Necessário para notificação e rastreio ponta a ponta | Acionamentos locais sem eventos dificultam observabilidade e consistência operacional |
