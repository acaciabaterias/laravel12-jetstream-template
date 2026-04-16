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
- **FR01 - Autenticação Básica**: Suporte ao login de usuários (via JWT ou Session).
- **FR02 - Controle de Papéis**: Cada usuário terá um papel atribuído.
- **FR03 - Políticas Granulares**: As operações DEVEM ser protegidas por `Policies` e `Gates` no Laravel com base no seu papel.
- **FR04 - Associação à Filial**: Usuários devem ser vinculados a uma ou mais Filiais.
- **FR05 - Logging de Acesso**: Salvar IP e dispositivo/user-agent do momento do acesso logado (`Log de Sessão`).

## User Stories
1. **Given** um Vendedor logado, **When** ele tenta excluir um cliente, **Then** a interface oculta o botão e o Laravel retorna erro `403` via HTTP.
2. **Given** um Entregador, **When** ele faz login mobile, **Then** seu IP e OS são registrados para fins de auditoria de segurança.

## Success Criteria
- **SC01**: Acesso proibido testado em todos os papéis (ex: Vendedor não consegue gerenciar usuários).
- **SC02**: Tabela pivô `filial_user` criada para associar acesso aos Tenants corretos.
