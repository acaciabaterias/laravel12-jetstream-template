# Microserviço Specification: MS-001 — Fiscal (SEFAZ) via ACBr

**Identificador**: `MS-001-FISCAL-ACBR`
**Status**: Ready for Implementation
**Tipo**: Microserviço Autônomo (projeto separado do ERP)
**Dependências ERP**: Módulo 005 (Vendas/Vales), Módulo 009 (Orquestrador Fiscal)

---

## Overview

O MS-001 é o microserviço responsável por **toda a comunicação com as Secretarias de Fazenda Estaduais (SEFAZ)** para emissão de NF-e (Nota Fiscal Eletrônica) e NFC-e (Nota Fiscal de Consumidor Eletrônica). Ele age como um **wrapper inteligente** ao redor do **ACBr (Aplicação Comercial Brasileira)**, abstraindo completamente a complexidade tributária/fiscal do ERP principal.

O ACBr é uma biblioteca consolidada (Delphi/FreePascal, licença LGPL) que já resolve:
- Assinatura digital (certificados A1/A3)
- Validação de XML contra schemas oficiais da SEFAZ
- Comunicação com WebServices SOAP da SEFAZ por estado
- Geração de DANFE em PDF
- Envio em lote de notas
- Modo de Contingência (Off-Line / SCAN / SVC-AN / SVC-RS)
- Cancelamento, Carta de Correção Eletrônica (CC-e) e Inutilização de numeração

Este MS **não reescreve** a lógica do ACBr. Ele recebe JSON do ERP, transforma em chamadas ACBr e devolve respostas padronizadas.

---

## Key Entities

- **NotaFiscalJob**: (id_uuid, vale_id, tipo [NFe/NFCe], payload_json, xml_assinado, chave_acesso, protocolo, status [pending/authorized/contingency/cancelled/error], tentativas, proxima_tentativa, created_at, updated_at)
- **DanfeStorage**: (id, nota_id, pdf_url, gerado_em)
- **ContingenciaQueue**: (id, nota_id, motivo, tentativas_realizadas, ultima_tentativa, proxima_tentativa, status)
- **AuditLog**: (id, nota_id, acao, payload_entrada, payload_saida, status_http, created_at)

---

## Functional Requirements

### FR-001-01: Emissão de NF-e
- O MS DEVE receber o payload JSON da venda e converter para XML conforme schemas da SEFAZ
- O ACBr DEVE assinar digitalmente o XML com o certificado A1 configurado
- O MS DEVE enviar o XML ao WebService da SEFAZ do estado do emitente
- Em caso de sucesso, o MS DEVE retornar: `chave_acesso`, `protocolo_autorizacao`, `xml_autorizado`, `danfe_url`
- O DANFE DEVE ser gerado automaticamente pelo ACBr após autorização

### FR-001-02: Emissão de NFC-e (PDV)
- O MS DEVE suportar emissão de NFC-e para vendas balcão com CPF ou CNPJ opcional do consumidor
- NFC-e DEVE ser emitida com QR Code válido para consulta pública
- Tempo máximo de resposta para NFC-e (operação síncrona): 3 segundos

### FR-001-03: Modo Contingência Automático
- Quando a SEFAZ estiver inacessível (timeout > 5s ou erro de conexão), o MS DEVE ativar automaticamente o modo SCAN ou SVC
- O payload DEVE ser armazenado na `ContingenciaQueue` com status `pending`
- O MS DEVE iniciar ciclo de retransmissão com exponential backoff: 1min → 5min → 30min → 2h → 6h
- Após SEFAZ reestabelecer conexão, o MS DEVE retransmitir todos os itens da fila automaticamente
- O ERP DEVE ser notificado via webhook/evento quando a nota sair da contingência

### FR-001-04: Cancelamento de NF-e
- O MS DEVE receber o evento `NF_CANCELAR` com `chave_acesso` e `justificativa` (mínimo 15 caracteres)
- O cancelamento DEVE ser solicitado dentro do prazo legal (até 24h após autorização, ou 168h para NFe com restrições específicas)
- O MS DEVE retornar o XML de cancelamento e o protocolo aprovado pela SEFAZ

### FR-001-05: Carta de Correção Eletrônica (CC-e)
- O MS DEVE aceitar solicitações de CC-e com os campos a serem corrigidos
- O MS DEVE validar que o campo solicitado pode ser corrigido por CC-e (campos proibidos: destinatário, valor, tributação)
- O MS DEVE rejeitar CC-e enviada após o prazo legal

### FR-001-06: Monitoramento de Certificado Digital
- O MS DEVE verificar a validade do certificado A1 ao inicializar e a cada 24 horas
- Se restarem ≤ 30 dias para vencimento, DEVE publicar evento `CERTIFICADO_EXPIRANDO` com dias restantes
- Se o certificado estiver expirado, DEVE publicar `CERTIFICADO_EXPIRADO` e rejeitar emissões com erro claro

### FR-001-07: Inutilização de Numeração
- O MS DEVE aceitar solicitações de inutilização de faixas de numeração não utilizadas
- A inutilização DEVE ser registrada e confirmada pela SEFAZ antes de retornar sucesso

---

## User Stories

### US-001-01: Emissão Automática ao Faturar
**Como** o sistema do ERP (Módulo 009 - Orquestrador),
**Quando** um Vale é convertido em Pedido de Venda (evento `VALE_FATURADO`),
**Quero** que o MS-001 receba o payload JSON, emita a NF-e junto à SEFAZ e devolva a chave de acesso e o PDF do DANFE,
**Para que** o balconista possa imprimir ou enviar o documento ao cliente em segundos.

**Critérios de Aceite:**
- Resposta com chave de acesso em < 3 segundos (SEFAZ disponível)
- DANFE gerado e armazenado com URL acessível
- Evento `NF_AUTORIZADA` publicado no broker

### US-001-02: Transparência na Contingência
**Como** balconista que está faturando em horário de pico,
**Quando** a SEFAZ do estado ficar inacessível,
**Quero** que o sistema continue funcionando sem travar minha operação,
**Para que** eu possa finalizar a venda e o sistema transmita automaticamente quando a SEFAZ voltar.

**Critérios de Aceite:**
- Venda salva localmente em < 1 segundo mesmo com SEFAZ indisponível
- Evento `NF_EM_CONTINGENCIA` publicado imediatamente
- Nota transmitida e autorizada automaticamente quando SEFAZ restaurar
- Evento `NF_AUTORIZADA` publicado após transmissão bem-sucedida

### US-001-03: Cancelamento de Nota pelo Fiscal
**Como** usuário com perfil Fiscal (Módulo 002),
**Quando** preciso cancelar uma NF-e emitida,
**Quero** solicitar o cancelamento fornecendo a justificativa,
**Para que** a SEFAZ registre o cancelamento e o XML seja armazenado.

**Critérios de Aceite:**
- Cancelamento aceito dentro do prazo legal
- XML de cancelamento armazenado e chave de acesso marcada como cancelada
- Evento `NF_CANCELADA` publicado no broker

---

## Eventos

### Eventos que o MS-001 **ESCUTA** (consome do broker):

| Evento | Publicado por | Descrição |
|---|---|---|
| `VALE_FATURADO` | Módulo 005 / 009 | Dispara emissão de NF-e ou NFC-e |
| `NF_CANCELAR` | Módulo 009 | Solicita cancelamento de uma nota |
| `NF_CCE_SOLICITAR` | Módulo 009 | Solicita Carta de Correção |
| `NF_INUTILIZAR` | Módulo 009 | Solicita inutilização de faixa numérica |

### Eventos que o MS-001 **PUBLICA** (produz no broker):

| Evento | Consumido por | Descrição |
|---|---|---|
| `NF_AUTORIZADA` | Módulo 009, 005 | Nota autorizada com chave e DANFE URL |
| `NF_EM_CONTINGENCIA` | Módulo 009 | Nota aguardando transmissão (SEFAZ offline) |
| `NF_CANCELADA` | Módulo 009, 005 | Cancelamento confirmado |
| `NF_ERRO` | Módulo 009 | Nota rejeitada com código e descrição do erro |
| `CERTIFICADO_EXPIRANDO` | Sistema de Alertas | Certificado com menos de 30 dias de validade |
| `CERTIFICADO_EXPIRADO` | Módulo 009, Alertas | Certificado inválido ou expirado |

---

## API Endpoints (REST Interno)

| Método | Endpoint | Descrição |
|---|---|---|
| `POST` | `/api/v1/nfe/emitir` | Emite NF-e a partir do payload JSON |
| `POST` | `/api/v1/nfce/emitir` | Emite NFC-e (PDV) |
| `POST` | `/api/v1/nfe/cancelar` | Solicita cancelamento de NF-e |
| `POST` | `/api/v1/nfe/cce` | Envia Carta de Correção Eletrônica |
| `POST` | `/api/v1/nfe/inutilizar` | Inutiliza faixa de numeração |
| `GET` | `/api/v1/nfe/{chave_acesso}` | Consulta status de uma NF-e |
| `GET` | `/api/v1/contingencia/fila` | Lista notas na fila de contingência |
| `GET` | `/api/v1/certificado/status` | Retorna validade do certificado A1 |
| `GET` | `/api/v1/health` | Health check do serviço e ACBr |

---

## Edge Cases

- **SEFAZ fora do ar**: Ativar modo contingência → armazenar na `ContingenciaQueue` → retransmitir automaticamente com exponential backoff (1min, 5min, 30min, 2h, 6h). Após 10 falhas em 24h, publicar alerta crítico.
- **Certificado A1 expirado**: Rejeitar emissões com erro `CERT_EXPIRED`, publicar `CERTIFICADO_EXPIRADO` e notificar administrador via webhook imediato.
- **CC-e enviada após prazo legal**: Rejeitar com erro `CCE_PRAZO_EXPIRADO` e retornar mensagem explicativa ao ERP.
- **Numeração de NF-e divergente**: Se o contador de numeração no ACBr desincronizar com o banco, o MS deve detectar e alertar antes de emitir (prevenindo duplicidades).
- **Rejeição da SEFAZ (código 4xx)**: Rejeições por dados inválidos (ex: CNPJ incorreto, CFOP inválido) NÃO devem ir para contingência — devem retornar `NF_ERRO` imediatamente ao ERP com o código e descrição da rejeição SEFAZ.
- **Nota emitida em duplicidade (idempotência)**: Toda requisição de emissão DEVE incluir um `correlation_id` (UUID do Vale). Se o ID já foi processado com sucesso, retornar a resposta cacheada sem reemitir.

---

## Success Criteria

- **SC-001-01**: NF-e autorizada em < 3 segundos com SEFAZ disponível (95° percentil)
- **SC-001-02**: 100% das notas em contingência são transmitidas automaticamente quando a SEFAZ restabelece conexão
- **SC-001-03**: Zero duplicidade de emissão — idempotência garantida por `correlation_id` único por Vale
- **SC-001-04**: Alertas de certificado expirado chegam ao ERP com ≥ 30 dias de antecedência
- **SC-001-05**: Taxa de rejeição por dados inválidos (vindos do ERP) < 1% após validação de schema local prévia
- **SC-001-06**: DANFE gerado e disponível via URL em < 5 segundos após autorização
