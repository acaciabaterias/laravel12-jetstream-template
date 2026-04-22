# Feature Specification: Módulo 006 - Logística e App do Entregador

**Feature Branch**: `006-logistics-delivery-app`
**Status**: Ready for Implementation
**Dependências**: Módulo 001 (Multi-Tenancy Isolado), Módulo 002 (RBAC), Módulo 003 (Cadastros Estruturais), Módulo 004 (Estoque e Logística Reversa), Módulo 005 (Vendas e Assistência)

## Contexto

Este módulo gerencia rotas de entrega, acompanhamento operacional, recebimentos móveis e sincronização offline do app do entregador dentro do banco de dados do tenant. O isolamento físico é garantido pelo `TenantConnectionMiddleware`, sem uso de `filial_id` ou escopos globais de tenant.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Rotas, pontos e recebimentos vivem no banco do tenant, sem `filial_id` |
| Mobile-First Field Operations | Operação offline, coleta em campo, recebimentos e atualização de rota |
| Comprehensive Inventory & Reverse Logistics | Ajuste de sucata e integração com devolução e saldo |
| Proactive Quality & Customer Service | Rastreabilidade da entrega e confirmação operacional |

## Key Entities

### Tenant Database
- **RotaEntrega**: `(id, entregador_id, data_rota, status, veiculo_id, observacoes, created_at, updated_at)`
- **PontoEntrega**: `(id, rota_entrega_id, vale_id, cliente_id, endereco_entrega, ordem_parada, status, peso_sucata_coletado, observacao, created_at, updated_at)`
- **RecebimentoMovel**: `(id, ponto_entrega_id, valor, metodo_pagamento, status_sincronizado, comprovante_path, created_at, updated_at)`
- **GeolocalizacaoEvento**: `(id, rota_entrega_id, ponto_entrega_id, latitude, longitude, tipo_evento, recorded_at)`
- **SyncEvento**: `(id, dispositivo_uuid, entidade_tipo, entidade_id, payload, status, created_at, updated_at)`
- **AuditLog**: `(id, user_id, action, table_name, record_id, old_values, new_values, ip, user_agent, created_at)`

## Functional Requirements

### FR-LOG-01: Operação Offline
- O app do entregador deve funcionar offline por até um turno operacional.
- Alterações locais devem ser persistidas no dispositivo até a sincronização.
- Ao reconectar, o sistema deve sincronizar alterações pendentes de forma ordenada.

### FR-LOG-02: Gestão de Rotas
- O sistema deve permitir montar rotas com múltiplos pontos de entrega.
- Cada rota deve ser atribuída a um entregador e opcionalmente a um veículo.
- O painel operacional deve exibir status das rotas ativas do tenant.

### FR-LOG-03: Ajuste Dinâmico de Sucata em Campo
- O entregador deve poder ajustar o peso real da sucata coletada no local.
- O sistema deve recalcular o impacto financeiro conforme regras do módulo 005.
- A diferença deve ser registrada e sincronizada com rastreabilidade.

### FR-LOG-04: Recebimentos Móveis
- O sistema deve permitir registrar recebimentos por múltiplos meios, como `pix`, `cartao` e `dinheiro`.
- O sistema deve suportar pagamento particionado em uma mesma entrega.
- Recebimentos offline devem ser sincronizados com status confiável.

### FR-LOG-05: Rastreamento Operacional
- O sistema deve registrar eventos de geolocalização relevantes para acompanhamento de rota.
- O painel tático deve exibir a evolução das rotas ativas com baixa latência.
- Apenas eventos necessários para auditoria devem ser persistidos no banco.

### FR-LOG-06: Trava de Encerramento
- O fechamento logístico só deve ocorrer quando recebimentos, sucata coletada e status do atendimento estiverem consistentes.
- O sistema deve impedir encerramento da rota quando houver divergência operacional pendente.

### FR-LOG-07: Auditoria
- Alterações em rotas, pontos, recebimentos e sincronizações críticas devem ser auditadas.
- O log deve conter usuário, ação, entidade, valores antes e depois, IP e user agent quando disponível.

## User Scenarios

### US01: Entrega offline com sincronização posterior
**Given** que o entregador está sem conectividade  
**When** ele registra peso da sucata, recebimento e status da parada  
**Then** os dados ficam preservados localmente e são sincronizados ao reconectar

### US02: Ajuste do net price em campo
**Given** que a sucata esperada na venda não corresponde ao peso coletado  
**When** o entregador ajusta o peso real no app  
**Then** o sistema recalcula a diferença financeira e registra a alteração

### US03: Monitoramento operacional
**Given** que há múltiplas rotas em andamento  
**When** o gestor abre o painel tático  
**Then** ele visualiza avanço de rotas, status das paradas e eventos recentes sem degradar a operação

## Edge Cases

- Sincronização duplicada do mesmo evento não deve gerar duplicidade de recebimento.
- Recebimento marcado como `contestavel` deve permitir continuidade apenas com política autorizada.
- Divergência entre peso previsto e peso coletado deve recalcular impacto sem perder o histórico anterior.
- Encerramento de rota com ponto pendente deve ser bloqueado.
- Queda de conexão durante sincronização deve permitir retomada idempotente.

## Success Criteria

- **SC-LOG-01**: O app permanece operacional offline por até 8 horas.
- **SC-LOG-02**: Eventos críticos de rota aparecem no painel em menos de 10 segundos quando online.
- **SC-LOG-03**: 100% dos eventos sincronizados são reconciliados sem duplicidade.
- **SC-LOG-04**: Ajustes de sucata e recebimentos móveis mantêm rastreabilidade completa.

## Dependencies

- Módulo 001 (Multi-Tenancy Isolado) para `TenantConnectionMiddleware`
- Módulo 002 (RBAC) para autenticação e papéis
- Módulo 003 (Cadastros Estruturais) para clientes e veículos relacionados
- Módulo 004 (Estoque e Logística Reversa) para conta sucata e reversa
- Módulo 005 (Vendas e Assistência) para vales, net price e fechamento comercial
