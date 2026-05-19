# Implementation Plan: Módulo 022 - Multi-Currency Support

**Branch**: `022-multi-currency-support` | **Date**: 2026-05-18 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/022-multi-currency-support/spec.md`

## Summary

Introduzir a camada central de múltiplas moedas com preferência monetária por operador, resolução de moeda por request, publicação governada de catálogo monetário e tabela de câmbio, inspeção reutilizável e rollback auditável da última publicação saudável.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 12  
**Primary Dependencies**: Eloquent, Livewire 4, Laravel Queue, PHPUnit, PostgreSQL  
**Storage**: PostgreSQL central para preferências monetárias, publicações de câmbio e relatórios de inconsistência; valores base existentes continuam em `BRL`  
**Testing**: PHPUnit (`Feature`, `Unit`) via `php artisan test --compact`  
**Target Platform**: ERP web multi-tenant com plano central administrativo  
**Project Type**: Laravel web application com control plane central  
**Performance Goals**: resolver moeda por request administrativo em tempo de resposta normal; materializar snapshot de taxas em < 1 min por publicação; concluir rollback em até 3 interações  
**Constraints**: não gravar estado monetário central em bancos tenant; não sobrescrever o valor base original; não adicionar dependências novas; não aceitar taxa zero ou negativa; preservar `BRL` como base inicial  
**Scale/Scope**: plano central com operadores `super_admin`, `support`, `billing`; moedas iniciais `BRL`, `USD`, `EUR`; recorte mínimo em billing, recovery, analytics e relatórios executivos

## ERP Modernization Context

**Modules**:
- Users and Profiles / RBAC
- Integration Backbone and Observability
- Platform Billing Control Plane
- Platform Payments and Reconciliation
- Platform Revenue Recovery
- Platform Commercial Analytics
- Executive Reporting Hub
- Platform Internationalization

**Constitution Check**:
- O módulo mantém toda a governança monetária no banco central. PASS
- Conversão monetária será projeção operacional, não substituição do valor base persistido. PASS
- O escopo técnico usa apenas recursos nativos do monolito Laravel e convenções já presentes. PASS

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Multi-tenancy and RBAC constraints are preserved. PASS
- Tests cover happy path and relevant failure path. PASS
- Stack constraints remain within approved technologies. PASS
- Documentation standards are applied proportionally to complexity. PASS
- Operational resilience controls are addressed when applicable. PASS
  - Publicações monetárias exigem taxa válida, fallback de moeda e rollback rastreável.
  - Preferências por operador não podem escapar da publicação ativa.

## Project Structure

### Documentation (this feature)

```text
specs/022-multi-currency-support/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── currency-inspection.md
│   └── currency-publication-events.md
└── tasks.md
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/Admin/
│   └── Requests/Admin/
├── Livewire/Admin/
├── Models/
├── Policies/
├── Providers/
└── Services/Platform/

config/
database/
├── factories/
└── migrations/
    └── central/

resources/views/
├── admin/
└── livewire/admin/

routes/
tests/
├── Feature/
└── Unit/
```

**Structure Decision**: Implementar a governança de múltiplas moedas no monolito Laravel com tabelas centrais para preferências por operador, publicações de catálogo monetário e taxas, inconsistências materiais e rollback auditável. Os módulos já entregues continuarão armazenando valor base, enquanto serviços centrais de projeção monetária entregarão leitura convertida no painel administrativo.

## Phase 0: Research

- Confirmar a estratégia de projeção monetária central sem mutar os valores base já persistidos.
- Definir conjunto mínimo de moedas iniciais, arredondamento e escala decimal para o recorte central.
- Definir como validar consistência de taxa e cobertura mínima de conversões obrigatórias.
- Definir rollback seguro da publicação ativa preservando preferências monetárias dos operadores.

## Phase 1: Design

- Modelar catálogo monetário, preferências por operador, publicações de taxa e relatórios de inconsistência.
- Definir contratos de inspeção JSON e eventos materiais de publicação/rollback.
- Descrever painel central de múltiplas moedas com preferência, publicação, inspeção e rollback.
- Registrar quickstart com evidência de publicação, troca de moeda e restauração da tabela anterior.

## Phase 2: Task Planning Readiness

- O `tasks.md` deve separar:
  - fundação central para catálogo monetário, preferências e publicações
  - serviços de resolução, conversão e validação de taxas
  - painel administrativo central e inspeção JSON
  - testes de troca de moeda, publicação, inconsistência e rollback
  - atualização de runbook e artefatos de produto

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Publicação governada de tabela de câmbio | Necessária para taxa ativa, inspeção e rollback auditável | Ler taxas fixas de config não preserva histórico nem governança |
| Projeção monetária sem alterar valor base | Necessária para preservar consistência histórica dos módulos já entregues | Converter e sobrescrever valores quebraria comparabilidade e trilha auditável |
