# Implementation Plan: Módulo 023 - Fiscal CFOP Export/Import

**Branch**: `023-fiscal-cfop-export-import` | **Date**: 2026-05-18 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/023-fiscal-cfop-export-import/spec.md`

## Summary

Introduzir a camada central de governança fiscal para exportação/importação com catálogo de CFOPs, cenários fiscais, publicação versionada, inspeção reutilizável e rollback auditável da última configuração saudável.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 12  
**Primary Dependencies**: Eloquent, Livewire 4, Laravel Queue, PHPUnit, PostgreSQL  
**Storage**: PostgreSQL central para catálogo CFOP, publicações fiscais e issue reports; consumo operacional projetado para o módulo `009`  
**Testing**: PHPUnit (`Feature`, `Unit`) via `php artisan test --compact`  
**Target Platform**: ERP web multi-tenant com plano central administrativo  
**Project Type**: Laravel web application com control plane central  
**Performance Goals**: responder consulta fiscal por cenário em tempo de request administrativo normal; materializar snapshot fiscal em < 1 min por publicação; concluir rollback em até 3 interações  
**Constraints**: não reescrever emissão do módulo `009`; não duplicar estado fiscal definitivo em bancos tenant neste módulo; não adicionar dependências novas; preservar governança central e trilha auditável  
**Scale/Scope**: plano central com operadores `super_admin`, `support`, `billing` e perfil fiscal; cenários iniciais de exportação direta, exportação indireta, importação para revenda e importação para consumo/industrialização

## ERP Modernization Context

**Modules**:
- Users and Profiles / RBAC
- Fiscal and Banking Orchestration
- Integration Backbone and Observability
- Executive Reporting Hub
- Platform Internationalization
- Multi-Currency Support

**Constitution Check**:
- O módulo mantém o catálogo fiscal em governança central e apenas projeta leitura operacional. PASS
- Rollback auditável e publicação versionada seguem o padrão já consolidado do backbone e dos módulos centrais recentes. PASS
- O escopo técnico permanece dentro do monolito Laravel e das convenções já presentes. PASS

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Multi-tenancy and RBAC constraints are preserved. PASS
- Tests cover happy path and relevant failure path. PASS
- Stack constraints remain within approved technologies. PASS
- Documentation standards are applied proportionally to complexity. PASS
- Operational resilience controls are addressed when applicable. PASS
  - Publicações fiscais exigem cobertura mínima de cenários e rollback rastreável.
  - Consultas por cenário não podem retornar classificação vazia sem fallback ou issue report.

## Project Structure

### Documentation (this feature)

```text
specs/023-fiscal-cfop-export-import/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── fiscal-inspection.md
│   └── fiscal-publication-events.md
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
└── Services/Fiscal/

config/
database/
├── factories/
└── migrations/
    └── central/

resources/views/
└── livewire/admin/

routes/
tests/
├── Feature/
└── Unit/
```

**Structure Decision**: Implementar a governança fiscal de exportação/importação no monolito Laravel com tabelas centrais para catálogo de CFOP, cenários, publicações, mappings e inconsistências. A leitura operacional ficará num painel central Livewire e numa inspeção JSON reutilizável, enquanto o consumo do módulo `009` será preparado via serviços centrais de resolução.

## Phase 0: Research

- Confirmar o recorte mínimo de cenários fiscais obrigatórios para exportação/importação.
- Definir a modelagem de CFOP, direção fiscal, flags de validação e natureza da operação sem acoplar demais ao fluxo transacional.
- Definir critérios de cobertura mínima e inconsistência material para publicação fiscal.
- Definir rollback seguro da publicação ativa preservando rastreabilidade e compatibilidade operacional.

## Phase 1: Design

- Modelar catálogo de CFOP, cenários fiscais, publicação de regras e issue reports.
- Definir contratos de inspeção JSON e eventos materiais de publicação/rollback.
- Descrever painel central de catálogo fiscal com consulta por cenário, cobertura e rollback.
- Registrar quickstart com evidência de publicação, consulta fiscal e restauração da configuração anterior.

## Phase 2: Task Planning Readiness

- O `tasks.md` deve separar:
  - fundação central para catálogo fiscal e publicações
  - serviços de resolução, cobertura e validação de regras
  - painel administrativo central e inspeção JSON
  - testes de consulta, publicação, inconsistência e rollback
  - atualização de runbook e artefatos de produto

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Publicação governada do catálogo fiscal | Necessária para coerência operacional, inspeção e rollback auditável | Configuração fixa em arquivo ou seed não preserva histórico nem governança |
| Snapshot explícito de cenários obrigatórios | Necessário para medir cobertura e evitar lacunas silenciosas | Validar apenas no momento da emissão desloca o problema para tarde demais |
