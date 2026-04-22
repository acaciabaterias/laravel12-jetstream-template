# Feature Specification: Módulo Isolated Tenancy (Supabase)

**Feature Branch**: `001-multi-filial-tenant`
**Status**: Architecture Refactored

## Overview
Implementação do suporte a múltiplos clientes (Assinantes) utilizando a arquitetura de **Banco de Dados por Cliente (Isolated Tenant)** via Supabase. O isolamento físico garante separação forte entre tenants e escalabilidade.

## Constitution Mapping
- **Princípio de Arquitetura: Multi-Tenancy Isolado (Database-per-Client)**
  - Este módulo implementa isolamento físico por cliente via conexão `tenant` resolvida em runtime.
  - Metadados administrativos e universais permanecem exclusivamente na conexão `central`.
  - O uso de `filial_id` ou `branch_id` como separação lógica do core é removido.
- **Princípio de Arquitetura: Ordem de Implementação**
  - Este módulo corresponde ao item 1 da ordem obrigatória de implementação: Multi-Tenancy Isolado.
- **Development Workflow & Quality Gates**
  - Requisitos e tarefas deste módulo devem manter rastreabilidade explícita com a constituição.
  - Fluxos novos devem possuir cobertura de testes para casos de sucesso e falha relevantes.

## Key Entities
### Cliente (Central)
- **Atributos**: ID, CNPJ, Razão Social, Subdomínio, Status (active/expired), Credenciais Supabase (host, password, keys).
- **Entidades relacionadas**: PlanoAssinatura, Assinatura, Fatura, UsuarioPlataforma e WhiteLabelConfig compõem o modelo operacional necessário para provisionamento, cobrança e customização.

## Functional Requirements
- **FR01 - Isolamento Físico por Cliente**:
  - Cada CNPJ cadastrado recebe uma instância de banco isolada.
  - Dados de clientes diferentes nunca coexistem na mesma base operacional.
  - O subdomínio (`{empresa}.erp.com`) identifica o tenant ativo para resolução da conexão.
  - O modelo legado com `filial_id` ou global scopes não deve ser usado para separação de tenants.
- **FR02 - Gestão Centralizada**: Uma base PostgreSQL Central armazena o catálogo de clientes e metadados de assinatura.
- **FR03 - White Label**: Suporte a branding customizado via `WhiteLabelConfig` (armazenado no banco do próprio tenant).
- **FR04 - Conexão Dinâmica**: Middleware `TenantConnectionMiddleware` resolve o tenant pelo subdomínio e reconfigura a conexão `tenant` em runtime.
- **FR05 - Provisionamento Automático**: Comando CLI/Interface para criação automática de projetos no Supabase e execução de migrações.
- **FR06 - Bloqueio por Inadimplência**: O acesso ao tenant deve ser negado quando o cliente possuir assinatura vencida ou faturas em atraso registradas na base `central`.

## User Scenarios
1. **Given** um acesso via subdomínio `cliente.erp.com`, **When** o middleware processa a requisição, **Then** as queries subsequentes são direcionadas para o banco específico do cliente no Supabase.
2. **Given** um novo assinante, **When** o administrador dispara o provisionamento, **Then** o sistema cria o banco, roda as migrações e libera o acesso.
3. **Given** um cliente com faturas em atraso, **When** uma requisição é feita para seu subdomínio, **Then** o sistema bloqueia o acesso antes de estabelecer a sessão de uso do tenant.
4. **Given** um tenant com configuração de White Label cadastrada, **When** o usuário acessa o ERP, **Then** a interface aplica cores, logo e branding da configuração ativa do tenant.

## Edge Cases
- Subdomínio inexistente não deve resolver conexão `tenant` e deve retornar resposta controlada.
- Cliente inativo ou expirado não deve acessar o tenant, mesmo com subdomínio válido.
- Cliente inadimplente deve ser bloqueado com base na situação da assinatura ou fatura na base `central`.
- Falha de comunicação com a base `central` deve impedir a resolução do tenant de forma segura.
- Falha parcial no provisionamento Supabase deve acionar rollback lógico do cadastro central ou marcar o tenant como provisionamento incompleto.
- Credenciais inválidas do tenant não devem expor segredos em logs ou respostas.

## Success Criteria
- **SC01**: O isolamento entre tenants é validado por testes de resolução de conexão, ausência de compartilhamento por `filial_id` e bloqueio de acesso indevido entre bases.
- **SC02**: Resolução de subdomínio em < 50ms.
- **SC03**: Automação funcional para criação e migração de novos bancos de clientes.
- **SC04**: Bloqueio de acesso para clientes com faturas em atraso via Banco Central.
