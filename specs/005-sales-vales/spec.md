# Feature Specification: Módulo de Vendas e "Vales"

**Feature Branch**: `005-sales-vales`
**Status**: Ready for Implementation
**Dependencies**: 001-multi-filial-tenant, 002-users-permissions-rbac, 003-structural-registries, 004-inventory-reverse-logistics

## Overview
Este módulo gerencia o fluxo de vendas, incluindo pedidos abertos (Vales), cálculo dinâmico de preço com sucata (Net Price), reserva de estoque e conversão para Pedido de Venda ou Ordem de Serviço.

## Key Entities
- **Vale**: (id, cliente_id, vendedor_id, filial_id, status [aberto/faturado/cancelado], data_criacao, data_faturamento, observacoes, created_by)
- **ItemVale**: (id, vale_id, bateria_id, quantidade, preco_unitario_original, preco_unitario_final, flag_devolveu_sucata, observacao)
- **PedidoVenda**: (id, vale_id, cliente_id, filial_id, data_emissao, valor_total, status [pendente/pago/cancelado], nf_referencia)
- **OrdemServico**: (id, vale_id, cliente_id, filial_id, data_abertura, status [aberta/em_andamento/concluida/cancelada], tecnico_responsavel, laudo, observacoes)

## Functional Requirements

### FR-VENDA-01: Gestão de Vales (Pedidos Abertos)
- Balconista pode criar, editar e cancelar Vales enquanto status for "aberto"
- Itens podem ser adicionados/removidos até o faturamento
- O Vale mantém o preço calculado no momento da criação (trava preço)

### FR-VENDA-02: Net Price (Preço Dinâmico com Sucata)
- Ao adicionar uma bateria, o sistema consulta a tabela de peso (módulo 004)
- Se flag_devolveu_sucata = TRUE: preco_final = preco_base
- Se flag_devolveu_sucata = FALSE: preco_final = preco_base + (peso_kg * valor_kg)
- O valor é recalculado em tempo real ao alterar a flag

### FR-VENDA-03: Reserva Imediata de Estoque
- Ao adicionar item ao Vale, reservar quantidade no estoque (módulo 004)
- Mover de "Disponível" para "Reservado"
- Ao remover item ou cancelar Vale, estornar reserva

### FR-VENDA-04: Conversão do Vale
- Vale pode ser convertido em Pedido de Venda (faturamento)
- Vale pode ser convertido em Ordem de Serviço (garantia/serviço)
- Após conversão, status do Vale muda para "faturado"
- Reserva de estoque é confirmada (vira saída definitiva)

### FR-VENDA-05: Controle de Sucata na Venda
- Registrar no ContaCorrenteSucata do cliente (débito em KG) quando a venda é finalizada SEM devolução
- Registrar crédito quando o cliente devolver a sucata fisicamente

### FR-VENDA-06: Filtros e Busca de Vales
- Buscar por: cliente, período, status, vendedor, filial
- Suporte offline para mobile (cache local)

## User Scenarios

### US01: Criação de Vale com Cálculo de Sucata
**Given** que um balconista está atendendo um cliente
**When** ele adiciona uma bateria ao Vale e desmarca "Devolveu Sucata"
**Then** o sistema deve:
- Consultar peso e valor da sucata (módulo 004)
- Calcular o acréscimo: (peso_kg * valor_kg)
- Atualizar o preço final no item
- Recalcular o total do Vale

### US02: Reserva de Estoque em Tempo Real
**Given** que existe apenas 1 unidade da bateria X em estoque
**When** o balconista adiciona a bateria X ao Vale do cliente A
**Then** o sistema deve:
- Reservar a unidade (Disponível → Reservado)
- Impedir que o cliente B adicione a mesma unidade em outro Vale
- Exibir "Saldo Insuficiente" para o cliente B

### US03: Conversão de Vale em Pedido de Venda
**Given** que um Vale está com status "aberto" e todos os itens reservados
**When** o balconista confirma o faturamento
**Then** o sistema deve:
- Converter o Vale em Pedido de Venda
- Confirmar a saída do estoque (Reservado → Saída)
- Gerar registro no Kardex
- Mudar status do Vale para "faturado"

### US04: Conversão de Vale em Ordem de Serviço
**Given** que um cliente precisa de serviço/garantia
**When** o balconista converte o Vale em Ordem de Serviço
**Then** o sistema deve:
- Criar a OS vinculada ao Vale
- Manter reserva de estoque (se aplicável)
- Permitir adicionar laudo e serviços extras

### US05: Cancelamento de Vale com Estorno
**Given** que um Vale tem itens reservados no estoque
**When** o balconista cancela o Vale antes do faturamento
**Then** o sistema deve:
- Estornar todas as reservas (Reservado → Disponível)
- Registrar log de auditoria do cancelamento
- Mudar status do Vale para "cancelado"

## Edge Cases
- **Estoque Insuficiente no Momento da Conversão**: Se o estoque foi reservado mas outra operação consumiu antes da conversão, o sistema deve impedir e alertar o balconista
- **Variação de Preço da Sucata**: O preço é travado no momento da criação do Vale (não recalcula se a tabela mudar)
- **Cliente sem Conta de Sucata**: Criar automaticamente com saldo zero ao finalizar primeira venda
- **Vale com Múltiplas Baterias e Sucata Mista**: Cada bateria tem sua própria flag de devolução
- **Desistência Parcial**: Remover itens do Vale deve estornar apenas as quantidades removidas

## Success Criteria
- **SC-VENDA-01**: Vale é criado e convertido em menos de 30 segundos
- **SC-VENDA-02**: Reserva de estoque é confirmada em menos de 500ms
- **SC-VENDA-03**: 100% das reservas são estornadas em caso de cancelamento
- **SC-VENDA-04**: Cálculo de Net Price é atualizado em tempo real ao alterar a flag de sucata
- **SC-VENDA-05**: Zero divergências entre reserva e estoque disponível
