# Implementation Plan: Módulo 012 - Platform Payments and Reconciliation

**Branch**: `012-platform-payments-reconciliation` | **Date**: 2026-05-08 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/012-platform-payments-reconciliation/spec.md`

## Summary

Fechar o ciclo financeiro externo do SaaS conectando faturas centrais do módulo `011` a gateways de cobrança, processando webhooks e retornos de forma idempotente, conciliando liquidações automaticamente quando seguro e segregando divergências operacionais para análise humana. A implementação deve permanecer no banco central, reutilizar o backbone `010` para eventos e replay operacional e preservar os estados comerciais já governados pelo `011`.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 12
**Primary Dependencies**: Eloquent, Livewire 4, Laravel Queue, Laravel HTTP Client, PostgreSQL, PHPUnit
**Storage**: PostgreSQL central para gateways, cobranças externas, retornos, conciliações e exceções; Redis para filas e processamento assíncrono auxiliar quando necessário
**Testing**: PHPUnit (`Feature`, `Unit`) executado via `php artisan test --compact`
**Target Platform**: ERP web multi-tenant em containers Linux com painel central de plataforma
**Project Type**: Laravel web application com backoffice SaaS central e integrações assíncronas
**Performance Goals**: emissão externa em < 30 s por fatura; exceção de conciliação visível em < 1 min; conciliação automática cobrindo >= 95% dos retornos válidos
**Constraints**: não misturar finanças SaaS com o financeiro tenant do módulo `008`; não duplicar cobrança externa para a mesma obrigação; tratar webhooks com idempotência; não introduzir dependências novas sem aprovação
**Scale/Scope**: múltiplos gateways ou perfis de cobrança, carteira recorrente SaaS, retornos assíncronos, estornos, chargebacks, baixa automática e operação central de exceções

## ERP Modernization Context

**Modules**:
- Multi-Tenancy Isolado
- Users and Profiles / RBAC
- Intelligent Financial Module
- Integration Backbone and Observability
- Platform Billing Control Plane

**Constitution Check**:
- O módulo impacta receita da plataforma, bloqueio comercial e consistência do estado financeiro central.
- O plano inclui backup/restore/rollback para emissão externa, liquidação, reconciliação e exceções operacionais.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Multi-tenancy and RBAC constraints are preserved. PASS
- Tests cover happy path and relevant failure path. PASS
- Stack constraints remain within approved technologies. PASS
- Documentation standards are applied proportionally to complexity. PASS
- Operational resilience controls are addressed when applicable. PASS
  - Recent valid backup evidence already documented in operational artifacts.
  - Restore rehearsal evidence already documented for the current environment.
  - Rollback path must be expanded to include emission, settlement and reconciliation reversal for this module.

## Project Structure

### Documentation (this feature)

```text
specs/012-platform-payments-reconciliation/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── payment-events.md
│   └── gateway-workflows.md
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

**Structure Decision**: Implementar pagamentos e reconciliação no próprio monolito Laravel usando banco central, serviços dedicados em `app/Services/Billing/`, jobs para emissão e processamento assíncrono, rotas administrativas centrais para inspeção e publicação de eventos pelo backbone `010` quando houver mudança financeira relevante.

## Phase 0: Research

- Definir a fronteira entre `011` e `012`, separando cobrança conceitual de pagamento externo e conciliação real.
- Definir estratégia de idempotência para emissão, webhook e replay operacional.
- Definir política de baixa automática versus segregação em exceção.
- Definir impacto comercial de liquidação, estorno, chargeback e falha persistente.

## Phase 1: Design

- Modelar gateway, cobrança externa, retorno, conciliação e exceção de reconciliação.
- Definir contratos de eventos financeiros centrais e fluxos operacionais de gateway.
- Descrever trilha administrativa para reprocessamento, baixa manual controlada e tratamento de divergência.
- Registrar quickstart para validação local e operacional do módulo central.

## Phase 2: Task Planning Readiness

- O `tasks.md` deve separar:
  - fundação central de dados (`gateways`, `cobrancas_externas`, `retornos`, `conciliacoes`, `excecoes`)
  - serviços de emissão, webhook, conciliação e reprocessamento
  - painel administrativo central e inspeção de divergências
  - testes de idempotência, liquidação, estorno, chargeback e replay
  - integração com backbone `010` e runbooks operacionais

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Estado financeiro externo persistido no banco central | Necessário para conciliar retornos, auditar divergências e evitar duplicidade | Guardar apenas status agregado na `fatura_saas` não sustenta rastreabilidade nem replay |
| Tratamento explícito de exceções de reconciliação | Necessário para chargeback, valor divergente e referência inválida | Ignorar divergências ou resolver inline quebra auditabilidade e reduz segurança operacional |
