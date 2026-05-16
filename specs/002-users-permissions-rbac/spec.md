# Feature Specification: Módulo 002 - Usuários e Perfis (RBAC)

**Feature Branch**: `002-users-permissions-rbac`
**Status**: Ready for Implementation
**Dependências**: Módulo 001 (Multi-Filial Tenant)

## Contexto

Este módulo gerencia usuários e permissões dentro de cada tenant (CNPJ), respeitando o isolamento físico por banco de dados. O Super Admin (Dono do SaaS) é gerenciado separadamente no banco central.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Database-per-client com autenticação dentro do tenant |
| RBAC | Papéis e permissões granulares dentro do tenant |
| Auditoria de Acesso | Logs com IP, dispositivo e timestamp |

## Key Entities

### Tenant Database (dentro do banco do CNPJ)
- **User**: `(id, name, email, password, papel, ativo, ultimo_login, ultimo_ip, created_at, updated_at)`
- **Papel**: `dono`, `gestor`, `vendedor`, `tecnico`, `estoquista`, `entregador`
- **Permissao**: `(id, nome, slug, descricao)`
- **PapelPermissao**: `(papel, permissao_id)`
- **AuditLogAcesso**: `(id, user_id, ip, user_agent, timestamp, sucesso)`

### Central Database (banco central)
- **UsuarioPlataforma**: `(id, name, email, password, papel, created_at)`
- **PapelPlataforma**: `super_admin`, `support`, `billing`

## Functional Requirements

### FR-RBAC-01: Usuário no Banco do Tenant
- Todo usuário operacional (`dono`, `gestor`, `vendedor`, `tecnico`, `estoquista`, `entregador`) DEVE existir no banco de dados do seu respectivo tenant.
- A tabela `users` NÃO DEVE ter coluna `filial_id`.
- A criação de usuários DEVE ser feita via formulário dentro do dashboard do tenant.

### FR-RBAC-02: Super Admin (Banco Central)
- O Super Admin DEVE existir no banco central (`usuarios_plataforma`).
- O Super Admin NÃO DEVE ter conta em nenhum banco de tenant.
- O Super Admin pode acessar qualquer tenant via seletor de contexto já implementado no módulo 001.

### FR-RBAC-03: Papéis e Permissões
- Papéis mínimos no tenant: `dono`, `gestor`, `vendedor`, `tecnico`, `estoquista`, `entregador`.
- Cada papel DEVE ter um conjunto de permissões associadas.
- O papel `dono` DEVE ter todas as permissões do tenant.
- O sistema PODE permitir criação de papéis customizados em evolução futura, sem bloquear a implementação base.

### FR-RBAC-04: Autenticação
- A autenticação DEVE ocorrer dentro do contexto do tenant após resolução do banco.
- O `TenantConnectionMiddleware` do módulo 001 DEVE ser o único responsável por definir qual banco usar.
- NÃO DEVE existir middleware `FilialIsolation`.

### FR-RBAC-05: Auditoria de Acesso
- O sistema DEVE registrar todo acesso de usuário com IP address, User Agent, timestamp e sucesso ou falha da autenticação.
- Os logs de acesso DEVEM ser armazenados no banco do tenant.
- O Super Admin NÃO DEVE ter acesso aos logs de acesso dos tenants.

### FR-RBAC-06: Usuário Ativo/Inativo
- O sistema DEVE permitir desativar e ativar usuários.
- Usuários inativos NÃO DEVEM conseguir autenticar.
- Usuários inativos DEVEM manter seus dados para auditoria.

## User Scenarios

### US01: Dono do tenant cria novo vendedor
**Given** que o usuário logado tem papel `dono`  
**When** ele acessa a tela de usuários e cria um novo usuário com papel `vendedor`  
**Then** o sistema deve:
- Criar o usuário no banco de dados do tenant atual
- Atribuir as permissões do papel `vendedor`
- Registrar o IP e User Agent do criador no log de auditoria

### US02: Super Admin acessa tenant
**Given** que o Super Admin está logado no banco central  
**When** ele seleciona um tenant via seletor de contexto  
**Then** o sistema deve:
- Resolver a conexão com o banco do tenant via `TenantConnectionMiddleware`
- Permitir acesso com papel de `super_admin` sem exigir conta no tenant

### US03: Tentativa de acesso sem permissão
**Given** que um usuário com papel `vendedor` tenta acessar a tela de configurações do tenant  
**When** ele acessa a URL `/admin/configuracoes`  
**Then** o sistema deve:
- Bloquear o acesso com HTTP 403
- Registrar a tentativa no log de auditoria

## Edge Cases

- Usuário inativo tenta logar: deve receber a mensagem `Usuário desativado. Contate o administrador.`
- Papel inválido: se o papel não existir no enum, retornar erro de validação.
- Tentativa de acesso a tenant diferente: o `TenantConnectionMiddleware` já bloqueia no módulo 001.
- Super Admin sem permissão operacional: o Super Admin não pode criar usuários dentro do tenant.

## Success Criteria

- **SC-RBAC-01**: 100% dos acessos são registrados com IP, User Agent e timestamp.
- **SC-RBAC-02**: Usuário de um tenant nunca acessa dados de outro tenant.
- **SC-RBAC-03**: Dono consegue criar usuários em menos de 30 segundos.
- **SC-RBAC-04**: Papéis aplicam permissões corretas com cobertura de testes automatizados.

## Dependencies

- Módulo 001 (Multi-Filial Tenant) para `TenantConnectionMiddleware` e resolução de banco.
