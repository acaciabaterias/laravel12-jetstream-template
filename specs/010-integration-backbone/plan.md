# Implementation Plan: Módulo 010 - Backbone de Integração e Observabilidade

**Branch**: `010-integration-backbone` | **Date**: 2026-05-06 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/010-integration-backbone/spec.md`

## Summary

Criar a espinha dorsal de integração do ERP para padronizar publicação e consumo de eventos, contratos versionados, API Gateway para chamadas síncronas controladas e observabilidade ponta a ponta entre ERP e microserviços. A abordagem prioriza outbox/inbox por tenant, rastreabilidade operacional, replay seguro e métricas acionáveis, sem duplicar a lógica de negócio já existente nos módulos `005-009`.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 12  
**Primary Dependencies**: Laravel Queue, Redis, Laravel Horizon, Laravel HTTP Client, Livewire 4, PostgreSQL, PHPUnit  
**Storage**: PostgreSQL tenant-aware para outbox/inbox/contratos/entregas + Redis para fila, transporte e coordenação operacional  
**Testing**: PHPUnit (`Feature`, `Unit`, `Contract`) executado via `php artisan test --compact`  
**Target Platform**: ERP web multi-tenant em Linux containers, integrado a microserviços autônomos  
**Project Type**: Laravel web application com integração assíncrona e painéis operacionais  
**Performance Goals**: despacho inicial de evento em < 1s após commit; replay manual em < 2 min; latência de inspeção operacional < 2s por tenant  
**Constraints**: preservar isolamento por tenant; impedir duplicidade funcional; tolerar indisponibilidade de broker/consumidor; não introduzir novo framework além da stack aprovada  
**Scale/Scope**: produtores principais nos módulos `005-009`, cinco microserviços externos, múltiplos tipos de evento por tenant e trilha operacional completa

## ERP Modernization Context

**Modules**:
- Sales and "Vales"
- Logistics (Delivery App)
- Inventory and Reverse Logistics
- Intelligent Financial Module
- Guarantees and Feedback
- Fiscal Module

**Constitution Check**:
- A feature impacta infraestrutura, fluxo de deploy e integridade de dados operacionais.
- O plano inclui backup/restore/rollback para outbox, inbox, contratos e estados de entrega.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Multi-tenancy and RBAC constraints are preserved. PASS
- Tests cover happy path and relevant failure path. PASS
- Stack constraints remain within approved technologies. PASS
- Documentation standards are applied proportionally to complexity. PASS
- Operational resilience controls are addressed when applicable. PASS
  - Recent valid backup evidence already documented in operational artifacts.
  - Restore rehearsal evidence already documented for the current environment.
  - Rollback path must be expanded to include integration state recovery for this module.

## Project Structure

### Documentation (this feature)

```text
specs/010-integration-backbone/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── event-catalog.md
│   └── gateway-contract.md
└── tasks.md
```

### Source Code (repository root)

```text
app/
├── Console/Commands/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Jobs/
├── Livewire/
├── Models/
├── Providers/
└── Services/
    ├── CircuitBreaker/
    ├── Contracts/
    └── Integration/

bootstrap/
config/
database/
├── factories/
└── migrations/
    └── tenant/

routes/
tests/
├── Feature/
├── Unit/
└── Contract/
```

**Structure Decision**: Implementar o backbone no próprio monolito Laravel, concentrando modelos tenant-aware, jobs, serviços de integração e painéis Livewire, enquanto contratos compartilhados permanecem documentados em `specs/010-integration-backbone/contracts/`.

## Phase 0: Research

- Confirmar padrão de outbox/inbox transacional compatível com o stack atual.
- Definir fronteira entre broker assíncrono e API Gateway síncrono sem reintroduzir acoplamento.
- Consolidar estratégia de versionamento de contratos e idempotência por evento.
- Definir modelo mínimo de observabilidade: métricas, dead-letter, replay e trilha de correlação.

## Phase 1: Design

- Modelar entidades de publicação, consumo, contrato e entrega.
- Definir contratos canônicos de eventos e de gateway.
- Descrever fluxo operacional de publicação, retry, replay e inspeção.
- Registrar quickstart para validação local e operacional.

## Phase 2: Task Planning Readiness

- O `tasks.md` deve separar:
  - fundação de dados (`outbox`, `inbox`, `entregas`, `contratos`)
  - serviços e jobs de despacho/consumo
  - gateway e políticas de autenticação/rate limit
  - observabilidade e painéis operacionais
  - testes de contrato, falha, retry e replay

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Estado operacional persistido para integrações | Requer replay, dead-letter e trilha auditável | Logs efêmeros e retries ad hoc não permitem recuperação segura |
| Catálogo explícito de contratos | Necessário para reduzir deriva entre ERP e microserviços | Convenção implícita por payload solto não sustenta evolução versionada |
