# Feature Specification: Módulo 009 - Orquestração Fiscal e Bancária

**Feature Branch**: `009-fiscal-bank-orchestrator`
**Status**: Ready for Implementation
**Dependências**: Módulo 001 (Multi-Tenancy Isolado), Módulo 002 (RBAC), Módulo 005 (Vendas e Assistência), Módulo 008 (Financeiro Inteligente)

## Contexto

Este módulo atua exclusivamente como camada de orquestração entre o ERP tenant-aware e microserviços externos responsáveis por emissão fiscal, boletos e processamento CNAB. O módulo não calcula impostos, não gera boletos localmente e não executa lógica bancária/fiscal de domínio; ele apenas encaminha payloads, persiste respostas relevantes, controla contingências e garante idempotência dentro do banco do tenant.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Notas, boletos e contingências vivem no banco do tenant, sem `filial_id` |
| Automated Financial Microservices | Delegação explícita para microserviços fiscal e bancário |
| Integrated Fiscal Compliance | Integração com emissão, retorno e contingência fiscal |
| Proactive Quality & Customer Service | Retentativas, idempotência e transparência operacional |

## Key Entities

### Tenant Database
- **NotaFiscalOrquestrada**: `(id, vale_id, chave_acesso, xml_path, status, ms_requisicao_id, created_at, updated_at)`
- **BoletoOrquestrado**: `(id, vale_id, nosso_numero, linha_digitavel, pdf_url, status, identificador_externo, created_at, updated_at)`
- **FilaContingencia**: `(id, tipo_integracao, payload, tentativas, proxima_tentativa, status, ultimo_erro, created_at, updated_at)`
- **CnabRemessa**: `(id, tipo_arquivo, nome_arquivo, status, arquivo_path, created_at, updated_at)`
- **CnabRetornoUpload**: `(id, cnab_remessa_id, nome_arquivo, status_processamento, log_processamento, created_at, updated_at)`
- **AuditLog**: `(id, user_id, action, table_name, record_id, old_values, new_values, ip, user_agent, created_at)`

## Functional Requirements

### FR-ORQ-01: Orquestração Fiscal
- O sistema deve enviar payloads de venda finalizada ao microserviço fiscal.
- O sistema deve persistir XML, chave de acesso e status retornados.
- O sistema não deve conter lógica local de cálculo tributário.

### FR-ORQ-02: Orquestração Bancária
- O sistema deve solicitar boletos e meios de pagamento ao microserviço bancário.
- O sistema deve persistir apenas os metadados retornados, como linha digitável, QR Code ou PDF.
- O sistema não deve gerar cobrança duplicada para a mesma origem.

### FR-ORQ-03: Fila de Contingência
- Em falhas externas, o sistema deve registrar a transação em fila de contingência.
- O sistema deve reprocessar pendências com política de retry controlada.
- Contingências acima do limite configurado devem gerar alerta operacional.

### FR-ORQ-04: Idempotência
- Cada requisição fiscal ou bancária deve possuir chave de idempotência.
- Reenvios tardios não podem gerar documentos duplicados.

### FR-ORQ-05: CNAB
- O sistema deve permitir download de remessas e upload de arquivos de retorno CNAB.
- O conteúdo de retorno deve ser encaminhado ao microserviço bancário para processamento.

### FR-ORQ-06: Transparência Operacional
- O vendedor deve conseguir finalizar a venda sem bloqueio total quando houver contingência externa.
- O sistema deve informar claramente o status da emissão ou cobrança ao usuário.

### FR-ORQ-07: Auditoria
- Emissões, falhas, reprocessamentos e uploads de CNAB devem ser auditados.
- O log deve conter usuário, ação, entidade, valores antes e depois, IP e user agent.

## User Scenarios

### US01: Emissão fiscal imediata
**Given** que uma venda foi finalizada com sucesso  
**When** o orquestrador envia o payload ao microserviço fiscal  
**Then** o ERP armazena a resposta fiscal e disponibiliza os artefatos retornados ao usuário

### US02: Contingência automática
**Given** que o microserviço fiscal ou bancário retorna erro temporário  
**When** o orquestrador tenta executar a integração  
**Then** a transação entra em fila de contingência sem travar a operação principal

### US03: Upload de CNAB retorno
**Given** que o operador financeiro possui um arquivo de retorno bancário  
**When** ele faz upload pelo painel  
**Then** o sistema registra o arquivo e o encaminha para processamento externo com rastreabilidade

## Edge Cases

- Falha repetida acima do limite de tentativas deve marcar a contingência como crítica.
- Reenvio da mesma emissão não deve gerar duplicidade de nota ou boleto.
- Arquivo CNAB inválido deve registrar erro sem quebrar o restante do painel.
- Resposta externa incompleta deve manter a requisição rastreável e pendente de revisão.

## Success Criteria

- **SC-ORQ-01**: Contingências não bloqueiam o fechamento da operação principal.
- **SC-ORQ-02**: O módulo não contém lógica fiscal ou bancária de negócio local.
- **SC-ORQ-03**: Emissões diretas bem-sucedidas retornam status utilizável em até 1.2 segundos.
- **SC-ORQ-04**: Reprocessamentos não geram duplicidade documental.

## Dependencies

- Módulo 001 (Multi-Tenancy Isolado) para `TenantConnectionMiddleware`
- Módulo 002 (RBAC) para autenticação e permissões
- Módulo 005 (Vendas e Assistência) para origem comercial das emissões
- Módulo 008 (Financeiro Inteligente) para integração com pagamentos e conciliação
