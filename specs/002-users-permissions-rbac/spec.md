# Feature Specification: Módulo de Usuários e Perfis (RBAC)

**Feature Branch**: `002-users-permissions-rbac`
**Status**: Ready for Implementation

## Overview
Gerenciamento de usuários e controle de acesso estrito com base em papéis (Role-Based Access Control) assegurados pela Constituição arquitetural.

## Key Entities
### Papel (Role)
- Dono, Gestor, Vendedor, Técnico, Estoquista, Entregador.
### Usuário (User)
- Email, Senha, Papel, status, Filial Padrão, Filiais Permitidas.

## Functional Requirements

### FR-002-01: Usuário Vinculado a UM CNPJ
- Todo usuário comum DEVE ter `filial_id` obrigatório (NOT NULL)
- Um usuário NÃO PODE ser associado a múltiplos CNPJs
- O papel do usuário é definido DENTRO do seu CNPJ
- Ao criar um usuário, o `filial_id` DEVE ser informado (exceto para super_admin)

### FR-002-02: Super Administrador (Dono do SaaS)
- O sistema DEVE ter um papel especial `super_admin`
- O `super_admin` NÃO tem `filial_id` (`filial_id = NULL`)
- O `super_admin` pode visualizar e gerenciar TODOS os CNPJs
- O `super_admin` pode criar/editar/bloquear qualquer assinante
- O `super_admin` tem um dashboard administrativo separado

### FR-002-03: Hierarquia de Papéis (por CNPJ)
Os papéis dentro de um CNPJ são:

- **dono**: Acesso total ao seu CNPJ, pode gerenciar usuários, configurações e assinatura
- **gestor**: Acesso total ao CNPJ, mas não pode gerenciar assinatura
- **vendedor**: Acesso a vendas, clientes, vales (não acessa estoque ou financeiro)
- **tecnico**: Acesso a OS (Ordens de Serviço), garantias, empréstimos
- **estoquista**: Acesso a estoque, compras, movimentações (não acessa vendas)

### FR-002-04: Middleware de Isolamento
- O sistema DEVE ter um middleware `FilialIsolation` aplicado às rotas protegidas
- O middleware DEVE verificar se o usuário autenticado tem acesso ao recurso solicitado
- Para `super_admin`: permite acesso a qualquer filial_id
- Para usuários comuns: bloqueia acesso a filial_id diferente do seu (HTTP 403)

### FR-002-05: Seeder de Super Admin
- O sistema DEVE ter um seeder que cria o usuário `super_admin` padrão
- O email do super_admin DEVE ser configurável via `.env` (SUPER_ADMIN_EMAIL)
- A senha do super_admin DEVE ser configurável via `.env` (SUPER_ADMIN_PASSWORD)

## User Stories
1. **Given** um Vendedor logado, **When** ele tenta excluir um cliente, **Then** a interface oculta o botão e o Laravel retorna erro `403` via HTTP.
2. **Given** um Entregador, **When** ele faz login mobile, **Then** seu IP e OS são registrados para fins de auditoria de segurança.

## Success Criteria
- **SC01**: Acesso proibido testado em todos os papéis (ex: Vendedor não consegue gerenciar usuários).
- **SC02**: Tabela pivô `filial_user` criada para associar acesso aos Tenants corretos.

### SC-002-01: Isolamento de Papéis por CNPJ
- 100% dos usuários de um CNPJ só enxergam dados do seu CNPJ
- Usuários de CNPJ A não aparecem em listagens do CNPJ B
- O `super_admin` tem visão global (dashboard exclusivo com todos os CNPJs)

### SC-002-02: Middleware Funcional
- Tentativas de acesso cross-CNPJ retornam HTTP 403
- O middleware não interfere nas rotas públicas (login, register)
- O super_admin consegue acessar qualquer rota de qualquer CNPJ
