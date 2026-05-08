# Implementation Plan: Módulo 014 - Platform Commercial Analytics

**Branch**: `014-platform-commercial-analytics` | **Date**: 2026-05-08 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/014-platform-commercial-analytics/spec.md`

## Summary

Adicionar a camada central de analytics comercial da plataforma sobre os módulos `011`, `012` e `013`, consolidando MRR, churn, inadimplência, recuperação, coortes, performance por canal e drill-down operacional. A implementação deve operar no banco central, reaproveitar sinais já estabilizados do backbone `010` e expor leitura executiva sem criar novo domínio transacional.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 12  
**Primary Dependencies**: Eloquent, Livewire 4, Laravel Queue, PHPUnit, PostgreSQL  
**Storage**: PostgreSQL central para snapshots, recortes analíticos, agregações por canal/coorte e referência de drill-down; Redis opcional para recalcular snapshots assíncronos quando necessário  
**Testing**: PHPUnit (`Feature`, `Unit`) executado via `php artisan test --compact`  
**Target Platform**: ERP web multi-tenant em containers Linux com painel central de plataforma  
**Project Type**: Laravel web application com backoffice SaaS central e leitura analítica executiva  
**Performance Goals**: painel executivo carregando recortes principais em < 3 interações; drill-down coerente com os módulos centrais; reconstrução de snapshot sem dupla contagem histórica  
**Constraints**: não introduzir dependências novas sem aprovação; não transformar analytics em fonte de verdade transacional; evitar dupla contagem entre billing, payments e recovery; manter rastreabilidade do indicador até a origem operacional  
**Scale/Scope**: carteira SaaS central completa, coortes de entrada, canais de cobrança/recuperação, métricas executivas e drill-down reutilizável por dashboard e inspeção

## ERP Modernization Context

**Modules**:
- Multi-Tenancy Isolado
- Users and Profiles / RBAC
- Integration Backbone and Observability
- Platform Billing Control Plane
- Platform Payments and Reconciliation
- Platform Revenue Recovery

**Constitution Check**:
- O módulo impacta decisão comercial, priorização executiva, retenção e pricing.
- O plano inclui backup/restore/rollback para snapshots analíticos, reconstrução e rastreabilidade de agregações.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Multi-tenancy and RBAC constraints are preserved. PASS
- Tests cover happy path and relevant failure path. PASS
- Stack constraints remain within approved technologies. PASS
- Documentation standards are applied proportionally to complexity. PASS
- Operational resilience controls are addressed when applicable. PASS
  - O módulo exigirá snapshots reprodutíveis, reconstrução auditável e drill-down consistente.
  - O rollback deve distinguir correção de agregação de alteração do dado operacional de origem.

## Project Structure

### Documentation (this feature)

```text
specs/014-platform-commercial-analytics/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── analytics-events.md
│   └── executive-workflows.md
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

**Structure Decision**: Implementar analytics comercial no monolito Laravel com snapshots e serviços centrais em `app/Services/Billing/`, jobs para reconstrução e agregação quando necessário, painéis Livewire para leitura executiva e endpoints de inspeção analítica para drill-down operacional.

## Phase 0: Research

- Definir fronteira entre dado operacional de origem e snapshot analítico reconstruível.
- Definir estratégia de cálculo para MRR, churn, inadimplência e recuperação sem dupla contagem.
- Definir segmentação mínima por coorte, canal e carteira.
- Definir regras de drill-down e reconstrução de snapshots após correções operacionais.

## Phase 1: Design

- Modelar snapshot executivo, recorte de coorte, performance por canal, insight de risco e referência de drill-down.
- Definir contratos de eventos analíticos e workflows executivos de leitura/reconstrução.
- Descrever painel executivo central, filtros analíticos e inspeção detalhada.
- Registrar quickstart para validação local de snapshots, drill-down e rebuild analítico.

## Phase 2: Task Planning Readiness

- O `tasks.md` deve separar:
  - fundação central de dados (`snapshots`, `coortes`, `performance_canais`, `insights_risco`, `drilldowns`)
  - serviços de agregação, segmentação, rebuild e consulta executiva
  - painel administrativo central e inspeção analítica
  - testes de MRR, churn, coortes, canais e drill-down
  - integração com backbone `010` e runbooks operacionais de rebuild

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Persistência explícita de snapshots analíticos | Necessária para comparação executiva, rebuild controlado e leitura consistente | Calcular tudo on-demand a partir das tabelas operacionais aumenta custo, fragilidade e pouca auditabilidade |
| Drill-down vinculado ao snapshot | Necessário para confiança executiva entre número agregado e composição real | Mostrar apenas métricas agregadas sem composição reduz utilidade prática para decisão |
