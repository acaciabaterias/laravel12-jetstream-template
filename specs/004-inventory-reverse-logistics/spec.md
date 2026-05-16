# Feature Specification: Módulo 004 - Estoque e Logística Reversa

**Feature Branch**: `004-inventory-reverse-logistics`
**Status**: Ready for Implementation
**Dependências**: Módulo 001 (Multi-Tenancy Isolado), Módulo 002 (RBAC), Módulo 003 (Cadastros Estruturais)

## Contexto

Este módulo gerencia entradas, saídas, ajustes, depósitos, importação de XML e conta sucata dentro do banco de dados de cada tenant. O isolamento físico é garantido pelo `TenantConnectionMiddleware` do módulo 001, portanto não existe particionamento lógico por `filial_id`.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Estoque e logística reversa existem no banco do tenant, sem `filial_id` |
| Comprehensive Inventory & Reverse Logistics | Controle de estoque, XML, conta sucata e shelf life |
| Proactive Quality | Auditoria das movimentações e rastreabilidade operacional |

## Key Entities

### Tenant Database
- **Deposito**: `(id, nome, tipo, status, created_at, updated_at)`
- **EstoqueMovimentacao**: `(id, bateria_id, deposito_id, user_id, tipo_operacao, origem, quantidade, justificativa, data_movimentacao, created_at, updated_at)`
- **EstoqueSaldo**: `(id, bateria_id, deposito_id, quantidade_atual, updated_at)`
- **XmlImportacao**: `(id, chave_nfe, fornecedor_id, status, log_erros, payload_xml, created_at, updated_at)`
- **ContaSucataMovimentacao**: `(id, entidade_tipo, entidade_id, tipo_movimento, quantidade_kg, valor_unitario, saldo_resultante, origem, created_at)`
- **AuditLog**: `(id, user_id, action, table_name, record_id, old_values, new_values, ip, user_agent, created_at)`

## Functional Requirements

### FR-INV-01: Múltiplos Depósitos
- O sistema deve permitir criar depósitos distintos dentro do tenant.
- O sistema deve permitir separar estoque por depósito, como `loja`, `almoxarifado` e `veiculo_entrega`.

### FR-INV-02: Movimentações de Estoque
- O sistema deve permitir entradas, saídas, transferências e ajustes de estoque.
- Toda movimentação deve exigir usuário autenticado e tipo de operação válido.
- Ajustes manuais devem exigir justificativa obrigatória.

### FR-INV-03: Saldos Consolidados
- O sistema deve manter saldo consolidado por bateria e depósito.
- O saldo consolidado deve refletir exatamente o extrato de movimentações.
- O sistema não deve permitir estoque negativo.

### FR-INV-04: Importação de XML
- O sistema deve permitir upload de XML de NF-e para automatizar entradas de estoque.
- O sistema deve associar itens importados às baterias cadastradas no tenant.
- Quando um item do XML não possuir correspondência, o sistema deve pausar a importação e solicitar vínculo manual ou cadastro rápido.

### FR-INV-05: Shelf Life
- O sistema deve monitorar o tempo em estoque das baterias com base na última entrada válida.
- O sistema deve alertar itens acima do limite configurado no dashboard.

### FR-INV-06: Conta Sucata
- O sistema deve manter débito e crédito de sucata para clientes e fornecedores.
- O sistema deve registrar origem, quantidade, valor unitário e saldo resultante.

### FR-INV-07: Auditoria e Rastreio
- Todas as movimentações de estoque e conta sucata devem ser auditadas.
- O log deve conter usuário, ação, entidade afetada, valores antes e depois, IP e user agent.

## User Scenarios

### US01: Entrada por XML
**Given** que um gestor autenticado recebe mercadorias  
**When** ele faz upload do XML da nota fiscal  
**Then** o sistema importa os itens compatíveis, cria movimentações de entrada e atualiza os saldos do tenant

### US02: Ajuste manual
**Given** que um estoquista autenticado identifica divergência física  
**When** ele registra um ajuste manual  
**Then** o sistema exige justificativa, grava a movimentação e atualiza o saldo sem permitir valor negativo

### US03: Alerta de shelf life
**Given** que uma bateria está parada acima do limite configurado  
**When** o gestor acessa o dashboard  
**Then** o sistema exibe o alerta de shelf life para ação operacional

## Edge Cases

- XML com produto não mapeado deve pausar o processamento e exigir confirmação manual.
- Saída ou ajuste que resulte em saldo negativo deve ser bloqueado.
- Transferência entre depósitos deve falhar se o depósito de origem não possuir saldo suficiente.
- Exclusão de depósito com saldo ativo deve ser bloqueada ou exigir esvaziamento prévio.
- Reprocessamento do mesmo XML deve impedir duplicidade por `chave_nfe`.

## Success Criteria

- **SC-INV-01**: Importação de XML com até 50 itens conclui em menos de 5 segundos.
- **SC-INV-02**: Saldos consolidados batem exatamente com o extrato de movimentações.
- **SC-INV-03**: Nenhuma operação permite estoque negativo.
- **SC-INV-04**: Alertas de shelf life aparecem no dashboard em menos de 2 segundos.
- **SC-INV-05**: 100% das movimentações de estoque e sucata são auditadas.

## Dependencies

- Módulo 001 (Multi-Tenancy Isolado) para `TenantConnectionMiddleware`
- Módulo 002 (RBAC) para autenticação e permissões
- Módulo 003 (Cadastros Estruturais) para baterias, fornecedores e clientes
