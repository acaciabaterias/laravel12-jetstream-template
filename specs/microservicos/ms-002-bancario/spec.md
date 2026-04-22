# Microserviço Specification: MS-002 — Bancário (Boletos, PIX e CNAB)

**Identificador**: `MS-002-BANCARIO`
**Status**: Ready for Implementation
**Tipo**: Microserviço Autônomo (projeto separado do ERP)
**Dependências ERP**: Módulo 008 (Financeiro Inteligente), Módulo 009 (Orquestração Fiscal e Bancária)

---

## Overview

O MS-002 é o microserviço responsável por **toda a comunicação com instituições bancárias** para geração de boletos registrados, cobranças PIX, remessas/retornos CNAB e conciliação de pagamentos recebidos. Ele abstrai completamente as APIs de cada banco (Bradesco, Itaú, Sicoob, etc.) e expõe uma interface unificada ao ERP.

O MS-002 **não executa regras financeiras** do ERP (juros, multas, parcelamentos). Ele recebe as instruções prontas, processa junto ao banco e devolve os resultados (linha digitável, QR Code PIX, confirmação de pagamento).

---

## Key Entities

- **Cobranca**: (id_uuid, erp_fatura_id, banco_id, tipo [boleto/pix/cartao], valor, vencimento, nosso_numero, linha_digitavel, qrcode_pix, pdf_url, status [pendente/pago/cancelado/expirado], pago_em, pago_valor)
- **RemessaCNAB**: (id, banco_id, arquivo_nome, arquivo_base64, tipo [REM/RET], status [gerado/processado/erro], registros_total, registros_ok, registros_erro, created_at)
- **BancoPerfil**: (id, nome, codigo_banco, agencia, conta, convenio, ambiente [homolog/producao], credenciais_json_encrypted)
- **WebhookRecebido**: (id, banco_id, payload_raw, evento, processado, created_at)

---

## Functional Requirements

### FR-002-01: Geração de Boleto Registrado
- O MS DEVE receber os dados de cobrança (valor, vencimento, sacado, instruções) e gerar boleto registrado junto ao banco
- O MS DEVE retornar: `nosso_numero`, `linha_digitável`, `URL do PDF do boleto`, `codigo_barras`
- Suporte obrigatório: Bradesco, Itaú, Sicoob, Banco do Brasil, Caixa Econômica Federal
- Multa e juros configuráveis por cobrança (passados pelo ERP na requisição)

### FR-002-02: Geração de Cobrança PIX
- O MS DEVE gerar cobranças PIX imediato (cob) e PIX com vencimento (cobv) via PIX API (BACEN)
- O MS DEVE retornar: `qr_code_string`, `qr_code_imagem_base64`, `link_pagamento`, `txid`
- O QR Code DEVE ser válido e consultável diretamente pelo pagador

### FR-002-03: Consulta de Status de Pagamento
- O MS DEVE oferecer endpoint de consulta de status de uma cobrança
- Em integração com bancos que suportam webhook, o MS DEVE receber notificações push de pagamento
- Em integração com bancos sem webhook, o MS DEVE realizar polling automático a cada 15 minutos

### FR-002-04: Cancelamento/Baixa de Cobrança
- O MS DEVE aceitar solicitação de baixa de boleto antes do vencimento
- O MS DEVE aceitar cancelamento de PIX e retornar confirmação do banco

### FR-002-05: Geração de Arquivo CNAB Remessa
- O MS DEVE gerar arquivos CNAB 240/400 (conforme banco) a partir de lista de cobranças
- O arquivo gerado DEVE ser disponibilizado para download pelo ERP
- O MS DEVE suportar múltiplos bancos em uma única operação em lote

### FR-002-06: Processamento de Arquivo CNAB Retorno
- O MS DEVE receber o arquivo `.RET` CNAB enviado pelo ERP (upload base64 ou multipart)
- O MS DEVE parsear o arquivo e retornar lista de pagamentos confirmados, rejeitados e ocorrências
- Cada linha de retorno DEVE ser mapeada para o `erp_fatura_id` correspondente
- Publicar evento `COBRANCA_PAGA` para cada boleto quitado identificado no retorno

### FR-002-07: Idempotência de Cobranças
- Toda requisição de criação de cobrança DEVE incluir `idempotency_key` (UUID da fatura do ERP)
- Se a chave já existir com status `pendente` ou `pago`, retornar a cobrança existente sem duplicar

---

## User Stories

### US-002-01: Geração Automática de Boleto ao Faturar
**Como** o ERP (via Módulo 009),
**Quando** uma venda é faturada com forma de pagamento "boleto",
**Quero** que o MS-002 gere o boleto registrado no banco e retorne a linha digitável,
**Para que** o balconista possa imprimir ou enviar ao cliente imediatamente.

**Critérios de Aceite:**
- Boleto gerado em < 3 segundos
- Linha digitável e PDF retornados
- Evento `COBRANCA_CRIADA` publicado

### US-002-02: Receber Confirmação de Pagamento via Webhook
**Como** gestor financeiro (Módulo 008),
**Quando** um cliente efetua o pagamento de um boleto,
**Quero** que o ERP seja notificado automaticamente (sem precisar importar CNAB),
**Para que** o contas a receber seja baixado em tempo real.

**Critérios de Aceite:**
- Webhook recebido do banco → evento `COBRANCA_PAGA` publicado em < 30 segundos
- `erp_fatura_id` corretamente identificado para baixa automática no Módulo 008

### US-002-03: Importação de CNAB Retorno
**Como** analista financeiro,
**Quando** recebo o arquivo `.RET` do banco,
**Quero** fazer upload pelo ERP e receber o resultado do processamento,
**Para que** múltiplas baixas sejam executadas de uma só vez.

**Critérios de Aceite:**
- Arquivo parcersado sem erros
- Eventos `COBRANCA_PAGA` publicados para cada boleto quitado
- Relatório de ocorrências (rejeições, instruções) retornado ao ERP

### US-002-04: PIX com QR Code para PDV
**Como** balconista,
**Quando** um cliente quer pagar com PIX,
**Quero** gerar um QR Code instantâneo na tela,
**Para que** o cliente escaneie e a confirmação apareça automaticamente.

**Critérios de Aceite:**
- QR Code PIX gerado em < 1 segundo
- Confirmação de pagamento recebida via webhook em < 10 segundos

---

## Eventos

### Eventos que o MS-002 **ESCUTA**:

| Evento | Publicado por | Descrição |
|---|---|---|
| `COBRANCA_CRIAR_BOLETO` | Módulo 009 | Solicita geração de boleto |
| `COBRANCA_CRIAR_PIX` | Módulo 009 | Solicita geração de cobrança PIX |
| `COBRANCA_CANCELAR` | Módulo 009 / 008 | Solicita baixa/cancelamento de cobrança |
| `CNAB_RET_PROCESSAR` | Módulo 009 | Arquivo CNAB retorno para processamento |

### Eventos que o MS-002 **PUBLICA**:

| Evento | Consumido por | Descrição |
|---|---|---|
| `COBRANCA_CRIADA` | Módulo 009, 008 | Boleto ou PIX gerado com dados de pagamento |
| `COBRANCA_PAGA` | Módulo 008 | Pagamento confirmado (webhook ou CNAB retorno) |
| `COBRANCA_EXPIRADA` | Módulo 008 | Boleto não pago após vencimento |
| `COBRANCA_CANCELADA` | Módulo 008 | Baixa confirmada pelo banco |
| `CNAB_RET_PROCESSADO` | Módulo 009 | Resultado do processamento do arquivo retorno |

---

## API Endpoints (REST Interno)

| Método | Endpoint | Descrição |
|---|---|---|
| `POST` | `/api/v1/boleto` | Gera boleto registrado |
| `POST` | `/api/v1/pix` | Gera cobrança PIX |
| `GET` | `/api/v1/cobranca/{id}` | Consulta status de cobrança |
| `DELETE` | `/api/v1/cobranca/{id}` | Baixa/cancela cobrança |
| `POST` | `/api/v1/cnab/remessa` | Gera arquivo CNAB remessa |
| `POST` | `/api/v1/cnab/retorno` | Processa arquivo CNAB retorno |
| `POST` | `/api/v1/webhook/{banco}` | Recebe webhooks do banco (PIX, boleto) |
| `GET` | `/api/v1/bancos` | Lista bancos configurados |
| `GET` | `/api/v1/health` | Health check |

---

## Edge Cases

- **Banco indisponível**: Retry automático com backoff (3 tentativas: 30s, 2min, 10min). Após falha, publicar `COBRANCA_ERRO` com motivo.
- **Boleto duplicado**: Idempotência por `idempotency_key` — retornar cobrança existente sem reemitir, prevenindo juros cobrados em duplicidade.
- **Webhook inválido (não autenticado)**: Validar assinatura HMAC do banco antes de processar. Descartar e logar tentativas não autenticadas.
- **CNAB com encodings diferentes**: Arquivos CNAB BAN/RET podem ter encoding EBCDIC ou ASCII. O parser deve detectar e normalizar automaticamente.
- **PIX expirado sem pagamento**: Após TTL do QR Code, publicar `COBRANCA_EXPIRADA` para que o ERP possa emitir nova cobrança ou acionar cobrança manual.
- **Valor de pagamento divergente (PIX parcial)**: Se banco permitir pagamento parcial (raro), registrar o valor pago real e publicar evento com flag `pagamento_parcial: true`.

---

## Success Criteria

- **SC-002-01**: Boleto gerado e linha digitável disponível em < 3 segundos (p95)
- **SC-002-02**: QR Code PIX gerado em < 1 segundo
- **SC-002-03**: 100% dos webhooks bancários autenticados resultam em evento `COBRANCA_PAGA` publicado em < 30 segundos
- **SC-002-04**: Zero cobranças duplicadas em 6 meses de operação (garantido por idempotência)
- **SC-002-05**: Arquivos CNAB retorno processados em < 60 segundos independente do tamanho
- **SC-002-06**: Suporte a ≥ 5 bancos diferentes com mesma interface de API
