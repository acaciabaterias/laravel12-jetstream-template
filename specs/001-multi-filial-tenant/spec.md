# Feature Specification: Módulo Multi-Filial / Tenant

**Feature Branch**: `001-multi-filial-tenant`
**Status**: Ready for Implementation

## Overview
Implementação do suporte base para múltiplas filiais (Tenants), garantindo isolamento absoluto de dados entre operações. 

## Key Entities
### Filial
- **Atributos Base**: ID, Nome de Fantasia, CNPJ, Status.

## Functional Requirements
- **FR01 - Isolamento de Dados**: Todas as queries e operações DEVEM ser filtradas por `filial_id` através de Global Scopes.
- **FR02 - Suporte SaaS**: Tabela `filiais` deve suportar planos, limites (usuários, estoque) e status de assinatura (trial, active, expired).
- **FR03 - White Label**: Suporte a branding customizado (logo, cores, favicon, CSS/JS) via `WhiteLabelConfig`.
- **FR04 - Resolution por Domínio**: Middleware `TenantResolver` deve identificar o tenant via subdomínio ou domínio personalizado.
- **FR05 - Seletor de Filial**: Dropdown no Dashboard permitindo a troca de contexto entre filiais do mesmo grupo.

## User Scenarios
1. **Given** um usuário logado associado a várias filiais, **When** ele clica no seletor do dashboard, **Then** ele pode alternar a visualização dos dados para outra filial específica.
2. **Given** um acesso via subdomínio (ex: `joao.erp.com`), **When** o sistema resolve o tenant, **Then** a interface reflete as cores e logo configurados no White Label.
3. **Given** uma assinatura expirada, **When** o usuário tenta acessar o dashboard, **Then** o sistema bloqueia o acesso com erro 402.

## Success Criteria
- **SC01**: Isolamento total de dados entre tenants.
- **SC02**: Resolução correta de tenant via subdomínio/domínio.
- **SC03**: Aplicação dinâmica de estilos White Label baseada no tenant resolvido.
- **SC04**: Bloqueio automático de funcionalidades por limite de plano.
