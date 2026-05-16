# Feature Specification: Módulo 007 - Garantias e Feedback

**Feature Branch**: `007-guarantees-feedback`
**Status**: Ready for Implementation
**Dependências**: Módulo 001 (Multi-Tenancy Isolado), Módulo 002 (RBAC), Módulo 003 (Cadastros Estruturais), Módulo 004 (Estoque e Logística Reversa), Módulo 005 (Vendas e Assistência)

## Contexto

Este módulo gerencia o ciclo pós-venda de garantias, baterias de empréstimo, notificações ao cliente e indicadores de retorno de produto dentro do banco de dados do tenant. O isolamento físico é garantido pelo `TenantConnectionMiddleware`, sem uso de `filial_id`.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Garantias, empréstimos e notificações vivem no banco do tenant, sem `filial_id` |
| Proactive Quality & Customer Service | Gestão de garantia, comunicação ao cliente e análise de retorno |
| RBAC | Fluxos de atendimento, laudo e gestão restritos por papel |
| Comprehensive Inventory & Reverse Logistics | Empréstimo de bateria e integração com devolução/logística |

## Key Entities

### Tenant Database
- **OrdemServicoGarantia**: `(id, cliente_id, bateria_id, vale_original_id, data_abertura, status, laudo, resultado, created_at, updated_at)`
- **BateriaEmprestimo**: `(id, os_garantia_id, bateria_usada_id, data_retirada, data_devolucao_prevista, data_devolucao_real, termo_arquivo_path, created_at, updated_at)`
- **NotificacaoWhatsApp**: `(id, os_garantia_id, cliente_telefone, status, mensagem, data_envio, identificador_externo, tracking_error, created_at, updated_at)`
- **IndiceRetornoProduto**: `(id, bateria_id, periodo_inicio, periodo_fim, total_vendidas, total_garantias, indice_calculado, created_at, updated_at)`
- **AuditLog**: `(id, user_id, action, table_name, record_id, old_values, new_values, ip, user_agent, created_at)`

## Functional Requirements

### FR-GAR-01: Abertura de OS de Garantia
- O sistema deve permitir abrir ordem de serviço de garantia vinculada a um vale anterior ou de forma avulsa.
- A OS deve registrar cliente, bateria avaliada, contexto de abertura e status inicial.

### FR-GAR-02: Bateria de Empréstimo
- O sistema deve permitir check-out de bateria provisória para o cliente durante a análise.
- O sistema deve gerar termo de responsabilidade em PDF com dados do cliente, produto e prazo de devolução.
- A retirada da bateria de empréstimo deve integrar com o estoque do módulo 004.

### FR-GAR-03: Laudo Técnico
- O técnico deve registrar laudo, anexos e resultado procedente ou improcedente.
- O sistema deve permitir atualizar o status da OS ao longo do fluxo técnico.

### FR-GAR-04: Cobrança de Improcedência
- Quando o laudo for improcedente, o sistema deve gerar a cobrança correspondente de serviço ou recarga.
- A cobrança deve ficar vinculada à OS e ao cliente para rastreabilidade.

### FR-GAR-05: Notificações ao Cliente
- Mudanças relevantes de status da OS devem gerar notificação para envio via gateway de WhatsApp.
- Falhas de envio não podem bloquear o fluxo operacional da OS.

### FR-GAR-06: Índice de Retorno
- O sistema deve recalcular o índice de retorno do produto com base em vendas e garantias.
- O indicador deve ficar disponível para consulta no contexto do produto do módulo 003.

### FR-GAR-07: Gestão de Devolução de Empréstimo
- O sistema deve alertar empréstimos vencidos.
- O sistema deve permitir registrar devolução da bateria provisória e encerrar a pendência.

### FR-GAR-08: Auditoria
- Abertura, atualização, laudo, empréstimo, cobrança e notificação devem ser auditados.
- O log deve conter usuário, ação, entidade, valores antes e depois, IP e user agent.

## User Scenarios

### US01: Abertura de garantia com bateria provisória
**Given** que um cliente retorna com uma bateria com defeito  
**When** o atendente abre uma OS de garantia e libera uma bateria provisória  
**Then** o sistema registra a OS, reserva a bateria de empréstimo e gera o termo em PDF

### US02: Laudo improcedente com cobrança
**Given** que o técnico concluiu que o problema não é defeito do produto  
**When** ele registra laudo improcedente  
**Then** o sistema gera a cobrança correspondente e agenda a comunicação ao cliente

### US03: Indicador de retorno no produto
**Given** que o gestor analisa uma bateria no cadastro de produtos  
**When** o sistema consulta os indicadores de garantia  
**Then** ele visualiza o índice de retorno atualizado para apoiar decisão de compra

## Edge Cases

- Bateria provisória não devolvida no prazo deve gerar alerta operacional.
- Falha no WhatsApp deve ser registrada sem quebrar o salvamento da OS.
- Cliente sem telefone válido deve manter a OS íntegra e registrar falha de comunicação.
- OS avulsa sem vale original deve continuar válida com rastreabilidade própria.
- Cobrança de improcedência não deve ser duplicada ao reprocessar o laudo.

## Success Criteria

- **SC-GAR-01**: O termo de empréstimo em PDF é disponibilizado em até 2 segundos.
- **SC-GAR-02**: Notificações de WhatsApp são despachadas em background sem bloquear a interface.
- **SC-GAR-03**: O índice de retorno reflete alterações de garantias e vendas sem consultas pesadas em tela.
- **SC-GAR-04**: 100% das transições críticas da OS são auditadas.

## Dependencies

- Módulo 001 (Multi-Tenancy Isolado) para `TenantConnectionMiddleware`
- Módulo 002 (RBAC) para autenticação e permissões
- Módulo 003 (Cadastros Estruturais) para produtos
- Módulo 004 (Estoque e Logística Reversa) para empréstimo e devolução
- Módulo 005 (Vendas e Assistência) para vínculo com vales e cobranças
