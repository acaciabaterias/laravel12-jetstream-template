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
- **FR02 - Tabela Base**: Criar a tabela `filiais` com campos essenciais.
- **FR03 - Seletor de Filial**: Implementar um dropdown no Dashboard permitindo a troca de contexto (apenas para usuários com acesso a mais de uma filial).
- **FR04 - Sessão**: O sistema deve manter o `filial_id` ativo salvo na sessão.

## User Stories
1. **Given** um usuário logado associado a várias filiais, **When** ele clica no seletor do dashboard, **Then** ele pode alternar a visualização dos dados para outra filial específica.
2. **Given** uma tentativa de acesso a um dado de outra filial (ex: `/api/clientes/2`), **When** a filial do registro for diferente da filial atual do usuário, **Then** o sistema retorna `403 Forbidden` ou exibe como não encontrado.

## Edge Cases
- O que acontece se a filial atual de um usuário for desativada? O sistema deve redirecioná-lo para a principal conta disponível ou realizar logout.

## Success Criteria
- **SC01**: Nenhum dado cruzado entre filiais em listagens do banco.
- **SC02**: Global scope obriga presença de `filial_id` na criação de novos registros das entidades.
