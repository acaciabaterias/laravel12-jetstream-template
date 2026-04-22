# Feature Specification: Módulo 005 - Vendas e Assistência (Vales e OS)

**Feature Branch**: `005-sales-service-os`
**Status**: Ready for Implementation
**Dependências**: Módulo 001 (Multi-Tenancy Isolado), Módulo 002 (RBAC), Módulo 003 (Cadastros Estruturais), Módulo 004 (Estoque e Logística Reversa)

## Contexto

Este módulo gerencia o fluxo comercial e de assistência técnica dentro do banco de dados do tenant: criação de vales, cálculo de preço com sucata, reserva de estoque, conversão em pedido de venda e abertura de ordem de serviço. O isolamento é garantido pelo `TenantConnectionMiddleware`, sem uso de `filial_id`.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Vales, pedidos e OS vivem no banco do tenant, sem `filial_id` |
| RBAC | Fluxos controlados por papéis como `gestor`, `vendedor` e `tecnico` |
| Comprehensive Inventory & Reverse Logistics | Reserva de estoque e integração com conta sucata |
| Proactive Quality & Customer Service | Conversão para OS, rastreabilidade e auditoria |

## Key Entities

### Tenant Database
- **Vale**: `(id, cliente_id, vendedor_id, status, data_criacao, data_faturamento, observacoes, created_by, created_at, updated_at)`
- **ItemVale**: `(id, vale_id, bateria_id, quantidade, preco_unitario_original, preco_unitario_final, flag_devolveu_sucata, observacao, created_at, updated_at)`
- **PedidoVenda**: `(id, vale_id, cliente_id, data_emissao, valor_total, status, nf_referencia, created_at, updated_at)`
- **OrdemServico**: `(id, vale_id, cliente_id, tecnico_responsavel_id, data_abertura, status, laudo, observacoes, created_at, updated_at)`
- **ReservaEstoque**: `(id, vale_id, item_vale_id, bateria_id, quantidade, status, created_at, updated_at)`
- **AuditLog**: `(id, user_id, action, table_name, record_id, old_values, new_values, ip, user_agent, created_at)`

## Functional Requirements

### FR-SALES-01: Gestão de Vales
- O sistema deve permitir criar, editar e cancelar vales enquanto o status for `aberto`.
- Itens podem ser adicionados ou removidos até a conversão final.
- O vale deve preservar o preço calculado no momento da criação.

### FR-SALES-02: Net Price com Sucata
- Ao adicionar uma bateria, o sistema deve consultar o peso e o valor da sucata do módulo 004.
- Se `flag_devolveu_sucata = true`, o preço final permanece no valor base.
- Se `flag_devolveu_sucata = false`, o preço final deve incluir o acréscimo baseado em peso e valor da sucata.
- O total do vale deve recalcular em tempo real.

### FR-SALES-03: Reserva de Estoque
- Ao adicionar item ao vale, o sistema deve reservar a quantidade correspondente no estoque do tenant.
- Ao remover item ou cancelar vale, a reserva deve ser estornada.
- O sistema deve impedir reserva quando o saldo disponível for insuficiente.

### FR-SALES-04: Conversão para Pedido de Venda
- O sistema deve converter vale em pedido de venda.
- A confirmação deve transformar a reserva em saída definitiva de estoque.
- O status do vale deve ser atualizado para `faturado`.

### FR-SALES-05: Conversão para Ordem de Serviço
- O sistema deve converter vale em ordem de serviço quando o atendimento exigir garantia ou serviço técnico.
- A OS deve manter vínculo com o vale original.
- O técnico responsável deve poder registrar laudo e observações complementares.

### FR-SALES-06: Controle de Sucata na Venda
- O sistema deve registrar débito na conta sucata do cliente quando a venda for concluída sem devolução de sucata.
- O sistema deve registrar crédito quando a sucata for entregue fisicamente depois.

### FR-SALES-07: Busca e Histórico
- O sistema deve permitir buscar vales por cliente, período, status e vendedor.
- O sistema deve permitir visualização otimizada em dispositivos móveis com suporte de cache read-only.

### FR-SALES-08: Auditoria
- Criação, edição, cancelamento e conversão de vale devem ser auditados.
- O log deve conter usuário, ação, entidade, valores antes e depois, IP e user agent.

## User Scenarios

### US01: Criação de vale com cálculo de sucata
**Given** que um vendedor está atendendo um cliente  
**When** ele adiciona uma bateria ao vale sem devolução de sucata  
**Then** o sistema calcula o acréscimo, atualiza o preço final e recalcula o total

### US02: Reserva em tempo real
**Given** que existe apenas uma unidade disponível de uma bateria  
**When** um vendedor adiciona essa bateria a um vale  
**Then** o sistema reserva a unidade e impede nova reserva concorrente

### US03: Conversão em pedido de venda
**Given** que um vale está aberto e com estoque reservado  
**When** o vendedor confirma o faturamento  
**Then** o sistema gera o pedido de venda, confirma a saída de estoque e fecha o vale

### US04: Conversão em ordem de serviço
**Given** que o cliente precisa de serviço técnico  
**When** o vale é convertido em OS  
**Then** o sistema cria a ordem de serviço vinculada e permite continuidade operacional do atendimento

### US05: Cancelamento de vale
**Given** que um vale possui itens reservados  
**When** ele é cancelado antes da conversão  
**Then** o sistema estorna as reservas e registra auditoria do cancelamento

## Edge Cases

- Estoque insuficiente no momento da reserva deve bloquear a inclusão do item.
- Estoque insuficiente no momento da conversão deve impedir faturamento e alertar o usuário.
- Alteração posterior no valor da sucata não deve recalcular vales já criados.
- Cliente sem conta sucata deve ter conta criada automaticamente ao primeiro movimento.
- Remoção parcial de itens do vale deve estornar apenas a reserva correspondente.

## Success Criteria

- **SC-SALES-01**: Vale é criado e convertido em menos de 30 segundos.
- **SC-SALES-02**: Reserva de estoque é confirmada em menos de 500ms.
- **SC-SALES-03**: 100% das reservas são estornadas em caso de cancelamento.
- **SC-SALES-04**: Cálculo de Net Price atualiza em tempo real ao alterar a flag de sucata.
- **SC-SALES-05**: Zero divergências entre reserva e estoque disponível.

## Dependencies

- Módulo 001 (Multi-Tenancy Isolado) para `TenantConnectionMiddleware`
- Módulo 002 (RBAC) para autenticação e permissões
- Módulo 003 (Cadastros Estruturais) para clientes e baterias
- Módulo 004 (Estoque e Logística Reversa) para reservas, saldo e conta sucata
