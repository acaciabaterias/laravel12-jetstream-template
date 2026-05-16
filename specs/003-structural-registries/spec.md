# Feature Specification: Módulo 003 - Cadastros Estruturais

**Feature Branch**: `003-structural-registries`
**Status**: Ready for Implementation
**Dependências**: Módulo 001 (Multi-Tenancy Isolado), Módulo 002 (RBAC)

## Contexto

Este módulo gerencia os cadastros estruturais do ERP: fabricantes, veículos, baterias e suas aplicações de compatibilidade. Todos os dados residem dentro do banco de dados de cada tenant, com isolamento físico garantido pelo `TenantConnectionMiddleware` do módulo 001.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Database-per-client, sem `filial_id` |
| Business Domain Specialization | Veículos, baterias e aplicações específicos do domínio |
| Proactive Quality | Auditoria completa de operações estruturais |

## Key Entities

### Tenant Database (dentro do banco do CNPJ)
- **Fabricante**: `(id, nome, codigo, created_at, updated_at)`
- **Veiculo**: `(id, fabricante_id, modelo, ano_inicio, ano_fim, motorizacao, atributos_dinamicos, created_at, updated_at)`
- **Bateria**: `(id, sku, marca, tipo, atributos_dinamicos, peso_sucata_kg, valor_base_sucata_kg, created_at, updated_at)`
- **Aplicacao**: `(id, veiculo_id, bateria_id, observacao, created_at, updated_at)`
- **AuditLog**: `(id, user_id, action, table_name, record_id, old_values, new_values, ip, user_agent, created_at)`

## Atributos Dinâmicos

- Baterias podem ter atributos variáveis por tipo, como `AGM`, `Chumbo-acido` e `Gel`.
- Veículos podem ter atributos variáveis por categoria, como `Carro`, `Moto` e `Caminhao`.
- Os atributos são armazenados como JSON e validados por schema.

## Functional Requirements

### FR-STR-01: CRUD de Fabricantes
- O sistema deve permitir criar, ler, atualizar e deletar fabricantes.
- O nome do fabricante deve ser único dentro do tenant.

### FR-STR-02: CRUD de Veículos
- O sistema deve permitir criar, ler, atualizar e deletar veículos.
- Veículo pertence a um fabricante.
- Atributos dinâmicos devem ser suportados via JSON.

### FR-STR-03: CRUD de Baterias
- O sistema deve permitir criar, ler, atualizar e deletar baterias.
- O SKU deve ser único dentro do tenant.
- Atributos dinâmicos devem ser suportados via JSON.
- Campos de logística reversa devem incluir `peso_sucata_kg` e `valor_base_sucata_kg`.

### FR-STR-04: Aplicações (Veículo ↔ Bateria)
- O sistema deve permitir vincular múltiplas baterias a um veículo.
- O sistema deve permitir adicionar observação técnica por vínculo.
- O sistema não deve permitir duplicidade do mesmo vínculo `veiculo + bateria`.

### FR-STR-05: Clonagem de Aplicações
- O sistema deve permitir clonar aplicações de um veículo para outro.
- Deve validar que o veículo de origem existe.
- Deve evitar duplicatas ao clonar.

### FR-STR-06: Busca de Veículos
- O sistema deve oferecer busca combinada por fabricante, modelo e ano.
- O sistema deve oferecer busca offline para mobile com cache local.

### FR-STR-07: Busca Reversa (Veículos por Bateria)
- O sistema deve exibir todos os veículos compatíveis com uma bateria.

### FR-STR-08: Auditoria
- Toda criação, alteração e exclusão deve ser logada.
- O log deve conter usuário, ação, tabela, valores antes e depois, IP e user agent.

## User Scenarios

### US01: Cadastro de fabricante
**Given** que um gestor autenticado acessa o módulo de fabricantes  
**When** ele cria um novo fabricante com nome e código válidos  
**Then** o sistema salva o fabricante no banco do tenant e registra a ação na auditoria

### US02: Vinculação de aplicação
**Given** que um usuário autorizado acessa um veículo existente  
**When** ele vincula uma bateria compatível com observação técnica  
**Then** o sistema cria a aplicação sem duplicidade e registra a operação

### US03: Busca reversa por bateria
**Given** que uma bateria possui veículos compatíveis cadastrados  
**When** o usuário acessa a busca reversa da bateria  
**Then** o sistema lista todos os veículos relacionados com resposta rápida

## Edge Cases

- SKU duplicado dentro do mesmo tenant deve ser bloqueado com mensagem de erro.
- Fabricante com veículos vinculados deve impedir exclusão ou aplicar soft delete com alerta explícito.
- Veículo com aplicações vinculadas deve impedir exclusão sem confirmação apropriada.
- Bateria com aplicações vinculadas deve seguir a mesma regra de proteção.
- Atributo dinâmico inválido deve falhar na validação do schema antes de persistir.

## Success Criteria

- **SC-STR-01**: CRUD de fabricante executa em menos de 2 segundos.
- **SC-STR-02**: CRUD de veículo executa em menos de 2 segundos.
- **SC-STR-03**: CRUD de bateria executa em menos de 2 segundos.
- **SC-STR-04**: Vinculação de aplicação executa em menos de 1 segundo.
- **SC-STR-05**: 100% das ações de CRUD são auditadas.
- **SC-STR-06**: Busca combinada retorna em menos de 500ms.

## Dependencies

- Módulo 001 (Multi-Tenancy Isolado) para `TenantConnectionMiddleware`
- Módulo 002 (RBAC) para autenticação e papéis
