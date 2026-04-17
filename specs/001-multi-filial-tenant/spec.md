# Feature Specification: Módulo Isolated Tenancy (Supabase)

**Feature Branch**: `001-isolated-tenancy`
**Status**: Architecture Refactored

## Overview
Implementação do suporte a múltiplos clientes (Assinantes) utilizando a arquitetura de **Banco de Dados por Cliente (Isolated Tenant)** via Supabase. O isolamento físico garante segurança absoluta e escalabilidade.

## Key Entities
### Cliente (Central)
- **Atributos**: ID, CNPJ, Razão Social, Subdomínio, Status (active/expired), Credenciais Supabase (host, password, keys).

## Functional Requirements
- **FR01 - Isolamento Físico**: Cada cliente possui seu próprio banco de dados no Supabase. Dados de clientes diferentes NUNCA coexistem no mesmo banco.
- **FR02 - Gestão Centralizada**: Uma base PostgreSQL Central armazena o catálogo de clientes e metadados de assinatura.
- **FR03 - White Label**: Suporte a branding customizado via `WhiteLabelConfig` (armazenado no banco do próprio tenant).
- **FR04 - Conexão Dinâmica**: Middleware `TenantConnectionMiddleware` resolve o tenant pelo subdomínio e reconfigura a conexão `tenant` em runtime.
- **FR05 - Provisionamento Automático**: Comando CLI/Interface para criação automática de projetos no Supabase e execução de migrações.

### FR-001-08: Um Banco por CNPJ
- Cada CNPJ cadastrado recebe uma instância de banco de dados isolada.
- O subdomínio (`{empresa}.erp.com`) é a chave de acesso única.

### FR-001-09: Isolamento Total
- Não existe compartilhamento de chaves estrangeiras (`filial_id`) entre clientes.
- A segurança é garantida no nível de conexão de rede/instância.

## User Scenarios
1. **Given** um acesso via subdomínio `cliente.erp.com`, **When** o middleware processa a requisição, **Then** as queries subsequentes são direcionadas para o banco específico do cliente no Supabase.
2. **Given** um novo assinante, **When** o administrador dispara o provisionamento, **Then** o sistema cria o banco, roda as migrações e libera o acesso.

## Success Criteria
- **SC01**: Isolamento físico absoluto (zero risco de vazamento de dados via queries).
- **SC02**: Resolução de subdomínio em < 50ms.
- **SC03**: Automação funcional para criação e migração de novos bancos de clientes.
- **SC04**: Bloqueio de acesso para clientes com faturas em atraso via Banco Central.
