# Implementation Plan: Módulo 013 - Platform Revenue Recovery

**Branch**: `013-platform-revenue-recovery` | **Date**: 2026-05-08 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/013-platform-revenue-recovery/spec.md`

## Summary

Adicionar a camada central de recuperação de receita do SaaS após os módulos `011` e `012`, cobrindo régua de cobrança, dunning multicanal, escalonamento humano, promessas de pagamento, reengajamento e métricas operacionais de recuperação. A implementação deve operar exclusivamente no banco central, reutilizar o backbone `010` para eventos e replay e respeitar os estados comerciais e financeiros já governados pelos módulos `011` e `012`.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 12  
**Primary Dependencies**: Eloquent, Livewire 4, Laravel Queue, Laravel Notifications, PHPUnit, PostgreSQL  
**Storage**: PostgreSQL central para políticas, casos, ações, compromissos e agregações de recuperação; Redis para filas e execução assíncrona de ações quando necessário  
**Testing**: PHPUnit (`Feature`, `Unit`) executado via `php artisan test --compact`  
**Target Platform**: ERP web multi-tenant em containers Linux com painel central de plataforma  
**Project Type**: Laravel web application com backoffice SaaS central e automações comerciais  
**Performance Goals**: iniciar régua elegível em < 5 min após atraso ou falha confirmada; refletir backlog operacional no painel em < 1 min; garantir consulta de próximo passo em até 3 interações  
**Constraints**: não mover workflow comercial para bancos tenant; não duplicar ações de cobrança no mesmo estágio/canal; respeitar promessas de pagamento vigentes; não reverter bloqueios ou liquidações sem sinal financeiro válido; não introduzir dependências novas sem aprovação  
**Scale/Scope**: carteira SaaS recorrente, múltiplos estágios de dunning, canais automatizados e humanos, reabertura por chargeback, replay de ações e métricas de recuperação central

## ERP Modernization Context

**Modules**:
- Multi-Tenancy Isolado
- Users and Profiles / RBAC
- Integration Backbone and Observability
- Platform Billing Control Plane
- Platform Payments and Reconciliation

**Constitution Check**:
- O módulo impacta receita recorrente, churn operacional, promessas comerciais e consistência de contato com assinantes.
- O plano inclui backup/restore/rollback para estágios da régua, compromissos manuais, replay de ações e eventos de reengajamento.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Multi-tenancy and RBAC constraints are preserved. PASS
- Tests cover happy path and relevant failure path. PASS
- Stack constraints remain within approved technologies. PASS
- Documentation standards are applied proportionally to complexity. PASS
- Operational resilience controls are addressed when applicable. PASS
  - O módulo exigirá trilha auditável de ações de cobrança, promessas e escalonamentos.
  - O rollback deve distinguir replay de comunicação de reversão de estado comercial já consolidado.

## Project Structure

### Documentation (this feature)

```text
specs/013-platform-revenue-recovery/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── recovery-events.md
│   └── dunning-workflows.md
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

**Structure Decision**: Implementar recuperação de receita no monolito Laravel com dados centrais dedicados, serviços em `app/Services/Billing/`, jobs assíncronos para avaliação de régua e execução de ações, painéis Livewire para operação central e publicação de eventos pelo backbone `010` quando houver transição relevante de estágio, escalonamento ou recuperação confirmada.

## Phase 0: Research

- Definir a fronteira entre cobrança automatizada, intervenção humana e reengajamento pós-recuperação.
- Definir estratégia de deduplicação por estágio, canal e obrigação financeira.
- Definir comportamento de promessas de pagamento e suspensão seletiva de ações automáticas.
- Definir critérios de escalonamento, reincidência e reabertura por chargeback ou estorno.

## Phase 1: Design

- Modelar política de recuperação, caso operacional, ação de cobrança, compromisso de pagamento e agregações de indicadores.
- Definir contratos de eventos de recuperação e workflows de dunning/replay.
- Descrever painel operacional central, filtros por estágio/canal e trilha auditável de compromissos.
- Registrar quickstart para validação local da régua, escalonamentos e encerramento automático por regularização.

## Phase 2: Task Planning Readiness

- O `tasks.md` deve separar:
  - fundação central de dados (`politicas`, `casos`, `acoes`, `compromissos`, `indicadores`)
  - serviços de avaliação da régua, deduplicação, escalonamento e encerramento
  - painel administrativo central e inspeção dos casos de recuperação
  - testes de atraso, falha de cobrança, promessa de pagamento, escalonamento e regularização
  - integração com backbone `010`, billing `011`, payments `012` e runbooks operacionais

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Persistência explícita de casos e ações da régua | Necessária para auditoria, replay e deduplicação multicanal | Derivar tudo apenas do status da fatura não sustenta histórico nem escalonamento |
| Suspensão seletiva por promessa de pagamento | Necessária para evitar contato indevido após acordo humano | Pausar toda a régua ou ignorar promessas torna a operação inconsistente |
