# Implementation Plan: Módulo 021 - Platform Internationalization

**Branch**: `021-platform-internationalization` | **Date**: 2026-05-18 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/021-platform-internationalization/spec.md`

## Summary

Introduzir a camada central de internacionalização da plataforma com preferência de idioma por operador, resolução de locale por request, publicação governada de idiomas suportados com fallback explícito, cobertura mínima de chaves centrais, inspeção reutilizável e rollback auditável.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 12  
**Primary Dependencies**: Eloquent, Livewire 4, Laravel localization helpers, Laravel Queue, PHPUnit, PostgreSQL  
**Storage**: PostgreSQL central para preferências de locale, publicações de idioma e relatórios de lacuna; arquivos `lang/` para catálogos traduzidos  
**Testing**: PHPUnit (`Feature`, `Unit`) via `php artisan test --compact`  
**Target Platform**: ERP web multi-tenant com plano central administrativo  
**Project Type**: Laravel web application com control plane central  
**Performance Goals**: resolver locale por request administrativo em tempo de resposta normal; detectar cobertura ausente em < 1 min por publicação; concluir rollback em até 3 interações  
**Constraints**: não gravar estado de idioma em bancos tenant; não aceitar locale fora da publicação ativa; não adicionar dependências novas; preservar fallback consistente para autenticação e painel central  
**Scale/Scope**: plano central com operadores `super_admin`, `support`, `billing`, idiomas `pt_BR`, `en`, `es` e recorte mínimo de autenticação, navegação e dashboard administrativo

## ERP Modernization Context

**Modules**:
- Users and Profiles / RBAC
- Integration Backbone and Observability
- Platform Billing Control Plane
- Platform Revenue Recovery
- Executive Reporting Hub
- Advanced Revenue Recovery Automation

**Constitution Check**:
- O módulo mantém todo o estado de governança de idioma no banco central. PASS
- A internacionalização é entregue com rollback auditável e sem alterar o isolamento multi-tenant. PASS
- O escopo técnico usa recursos nativos do Laravel (`lang`, JSON translations, `App::setLocale`). PASS

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Multi-tenancy and RBAC constraints are preserved. PASS
- Tests cover happy path and relevant failure path. PASS
- Stack constraints remain within approved technologies. PASS
- Documentation standards are applied proportionally to complexity. PASS
- Operational resilience controls are addressed when applicable. PASS
  - Publicações de idioma exigem fallback explícito e rollback rastreável.
  - Preferências por operador não podem escapar da publicação ativa.

## Project Structure

### Documentation (this feature)

```text
specs/021-platform-internationalization/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── locale-inspection.md
│   └── locale-publication-events.md
└── tasks.md
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/Admin/
│   ├── Middleware/
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

lang/
resources/views/
├── admin/
└── livewire/admin/

routes/
tests/
├── Feature/
└── Unit/
```

**Structure Decision**: Implementar a governança de idioma no monolito Laravel, usando arquivos `lang/*.json` como fonte de strings e tabelas centrais para preferência do operador, publicação de bundles, cobertura e lacunas detectadas. A resolução de locale será feita por middleware, enquanto painel e inspeção ficarão no recorte administrativo central via Livewire e controller JSON.

## Phase 0: Research

- Confirmar a abordagem nativa do Laravel 12 para publicar `lang`, usar JSON translations, aplicar `App::setLocale()` por request e manter fallback.
- Definir o conjunto mínimo de chaves obrigatórias para o recorte central.
- Definir como medir cobertura por locale sem criar pipeline de tradução fora do Laravel.
- Definir rollback seguro da publicação ativa preservando preferências já salvas.

## Phase 1: Design

- Modelar preferência por operador, publicações de locale e relatórios de lacuna.
- Definir contratos de inspeção JSON e eventos materiais de publicação/rollback.
- Descrever painel central de internacionalização com mudança de preferência, cobertura e rollback.
- Registrar quickstart com evidência de publicação, troca de idioma e restauração da publicação anterior.

## Phase 2: Task Planning Readiness

- O `tasks.md` deve separar:
  - fundação central para preferência de locale e publicações
  - middleware e serviços de resolução/cobertura
  - painel administrativo central e inspeção JSON
  - testes de troca de idioma, publicação, cobertura e rollback
  - atualização de `lang/`, runbook e artefatos de produto

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Publicação governada de locales suportados | Necessária para fallback, cobertura e rollback auditável | Configuração fixa apenas em `.env` não preserva histórico nem governança por operador |
| Relatório explícito de chaves ausentes | Necessário para liberar idiomas sem lacunas silenciosas | Confiar somente no fallback oculta degradação e impede inspeção operacional |
