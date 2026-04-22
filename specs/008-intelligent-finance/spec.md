# Feature Specification: Módulo 008 - Financeiro Inteligente

**Feature Branch**: `008-intelligent-finance`
**Status**: Ready for Implementation
**Dependências**: Módulo 001 (Multi-Tenancy Isolado), Módulo 002 (RBAC), Módulo 003 (Cadastros Estruturais), Módulo 004 (Estoque e Logística Reversa), Módulo 005 (Vendas e Assistência), Módulo 007 (Garantias e Feedback)

## Contexto

Este módulo gerencia conciliação bancária, projeção de fluxo de caixa, apuração de margem real e integração financeira com vendas, garantias e logística dentro do banco de dados do tenant. O isolamento físico é garantido pelo `TenantConnectionMiddleware`, sem uso de `filial_id`.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Contas, transações e projeções vivem no banco do tenant, sem `filial_id` |
| Automated Financial Microservices | Conciliação via APIs bancárias e processamento assíncrono |
| Proactive Quality & Customer Service | Cobrança automática de improcedência e alertas operacionais |
| RBAC | Controle de acesso para caixa, financeiro e gestão |

## Key Entities

### Tenant Database
- **ContaBancaria**: `(id, banco, agencia, conta, tipo, token_api, status, created_at, updated_at)`
- **TransacaoFinanceira**: `(id, conta_bancaria_id, tipo, valor, data_transacao, status_conciliado, origem_tipo, origem_id, descricao, created_at, updated_at)`
- **FluxoCaixaProjetado**: `(id, data_referencia, saldo_inicial, total_receber, total_pagar, saldo_projetado, created_at, updated_at)`
- **MargemLucroReal**: `(id, bateria_id, periodo_inicio, periodo_fim, valor_venda, custo_aquisicao, frete, imposto, comissao, margem_calculada, created_at, updated_at)`
- **ConciliacaoPendente**: `(id, transacao_financeira_id, motivo, payload_bancario, status, created_at, updated_at)`
- **FechamentoContabil**: `(id, competencia, status, fechado_em, fechado_por, created_at, updated_at)`
- **AuditLog**: `(id, user_id, action, table_name, record_id, old_values, new_values, ip, user_agent, created_at)`

## Functional Requirements

### FR-FIN-01: Integração Bancária
- O sistema deve consumir APIs bancárias ou gateways financeiros para importar transações.
- A conciliação automática deve tentar associar recebimentos e pagamentos às origens do ERP.
- Transações ambíguas devem permanecer pendentes para averiguação humana.

### FR-FIN-02: Fluxo de Caixa Projetado
- O sistema deve consolidar saldo atual, contas a receber e contas a pagar por data.
- O painel deve destacar déficits e folgas projetadas.

### FR-FIN-03: Margem de Lucro Real
- O sistema deve calcular margem real por produto considerando custos, frete, impostos, comissões e efeito da sucata.
- O indicador deve ser acessível no contexto analítico do produto.

### FR-FIN-04: Cobrança de Improcedência
- Quando uma OS de garantia for marcada como improcedente, o sistema deve gerar a cobrança correspondente.
- A cobrança deve ser registrada como transação financeira rastreável.

### FR-FIN-05: Lançamentos Manuais
- O sistema deve permitir lançamentos manuais de receitas e despesas com trilha de auditoria.
- Lançamentos devem respeitar permissões de acesso por papel.

### FR-FIN-06: Fechamento Contábil
- O sistema deve bloquear edição de períodos já fechados contabilmente.
- Qualquer tentativa de modificação em competência encerrada deve falhar com bloqueio explícito.

### FR-FIN-07: Auditoria Financeira
- Conciliação, lançamentos, cobranças e bloqueios contábeis devem ser auditados.
- O log deve conter usuário, ação, entidade, valores antes e depois, IP e user agent.

## User Scenarios

### US01: Conciliação automática
**Given** que o banco importou transações do dia anterior  
**When** o gestor financeiro acessa a tela de conciliação  
**Then** o sistema já apresenta os matches automáticos e destaca apenas as pendências reais

### US02: Projeção de caixa
**Given** que existem contas a pagar e a receber nos próximos dias  
**When** o analista financeiro abre o painel projetado  
**Then** o sistema calcula o saldo futuro e sinaliza risco de déficit

### US03: Cobrança de garantia improcedente
**Given** que uma garantia foi classificada como improcedente  
**When** o fluxo é concluído no módulo 007  
**Then** o sistema cria automaticamente a transação financeira correspondente

## Edge Cases

- Transações bancárias com múltiplos candidatos de match devem ficar pendentes.
- Token bancário inválido não pode quebrar o painel financeiro; deve gerar erro rastreável.
- Fechamento contábil já encerrado deve impedir edição retroativa.
- Falha na cobrança automática de improcedência deve gerar alerta financeiro sem perder o vínculo com a OS.
- Duplicidade de importação bancária deve ser bloqueada por identificador externo.

## Success Criteria

- **SC-FIN-01**: A conciliação automática atinge pelo menos 95% de acerto nos matches simples.
- **SC-FIN-02**: O fluxo de caixa projetado responde em menos de 2.5 segundos.
- **SC-FIN-03**: Cobranças de improcedência geram transação financeira rastreável sem intervenção manual.
- **SC-FIN-04**: Períodos fechados contabilmente não sofrem alterações indevidas.

## Dependencies

- Módulo 001 (Multi-Tenancy Isolado) para `TenantConnectionMiddleware`
- Módulo 002 (RBAC) para autenticação e permissões
- Módulo 003 (Cadastros Estruturais) para produtos
- Módulo 004 (Estoque e Logística Reversa) para custos e sucata
- Módulo 005 (Vendas e Assistência) para recebíveis comerciais
- Módulo 007 (Garantias e Feedback) para cobrança de improcedência
