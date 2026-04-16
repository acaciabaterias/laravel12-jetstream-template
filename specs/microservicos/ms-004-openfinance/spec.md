# Microserviço Specification: MS-004 — Open Finance (Extratos e Conciliação)

**Identificador**: `MS-004-OPENFINANCE`
**Status**: Ready for Implementation
**Tipo**: Microserviço Autônomo (projeto separado do ERP)
**Dependências ERP**: Módulo 008 (Financeiro Inteligente / Conciliação Bancária)

---

## Overview

O MS-004 é o microserviço responsável pela **captura automatizada de extratos bancários em tempo real** via Open Finance Brasil (regulamentação do Banco Central) e/ou APIs de agredadores financeiros (Pluggy, Belvo). Ele alimenta o motor de conciliação bancária do Módulo 008 com transações padronizadas de qualquer banco conectado.

**Contexto regulatório:**
O Open Finance Brasil (anteriormente Open Banking) obriga os bancos participantes a expor APIs padronizadas para que clientes possam, mediante consentimento, compartilhar seus dados financeiros com terceiros. O MS-004 atua como esse "terceiro confiável".

**Modelo de operação:**
1. Usuário autoriza acesso (OAuth 2.0 com PKCE) na plataforma do banco
2. MS-004 armazena o consentimento e token de acesso (com refresh automático)
3. Cron job a cada 4 horas busca novas transações de todos os consentimentos ativos
4. Transações são normalizadas para formato JSON padronizado e publicadas para o Módulo 008

---

## Key Entities

- **Consentimento**: (id, empresa_id, banco_nome, banco_codigo, status [ativo/expirado/revogado], access_token_encrypted, refresh_token_encrypted, expira_em, escopo, created_at)
- **TransacaoBancaria**: (id, consentimento_id, tx_id_original, data_lancamento, data_valor, descricao, valor, tipo [credito/debito], categoria, conta_origem, conta_destino, deduplicacao_hash, created_at)
- **ExtratoCapturaLog**: (id, consentimento_id, status [success/error/partial], total_transacoes, periodo_de, periodo_ate, duracao_ms, erro_descricao, created_at)
- **BancoProvider**: (id, nome, codigo_banco, provider [pluggy/belvo/direto], api_client_id, api_client_secret_encrypted, ativo)

---

## Functional Requirements

### FR-004-01: Gestão de Consentimento OAuth
- O MS DEVE iniciar o fluxo OAuth 2.0 com PKCE gerando URL de autorização para redirecionamento ao banco
- O MS DEVE receber o callback OAuth e trocar o authorization code por access_token + refresh_token
- O MS DEVE armazenar tokens de forma criptografada (AES-256-GCM)
- O MS DEVE executar refresh automático de access_token antes da expiração (margem de 10 minutos)
- Quando o refresh falhar (consentimento revogado), o MS DEVE marcar como `expirado` e publicar `CONSENTIMENTO_EXPIRADO`

### FR-004-02: Captura de Extratos por Agendamento
- O MS DEVE executar captura de transações a cada 4 horas para todos os consentimentos `ativos`
- A captura DEVE buscar o período desde a última captura bem-sucedida até agora
- O MS DEVE suportar múltiplos bancos simultaneamente (execuções paralelas por consentimento)
- Cada captura DEVE ser registrada em `ExtratoCapturaLog`

### FR-004-03: Normalização de Transações
- O MS DEVE converter transações no formato específico de cada banco/provider para o formato JSON padronizado interno
- O formato padronizado DEVE incluir: `tx_id`, `data`, `valor`, `tipo`, `descricao`, `categoria_sugerida`, `banco_origem`
- Campos opcionais do banco (como categoria, nome do favorecido) DEVEM ser mapeados se disponíveis

### FR-004-04: Deduplicação de Transações
- Toda transação DEVE receber um `deduplicacao_hash` gerado a partir de: `data + valor + descricao + conta`
- Antes de persistir, verificar se o hash já existe no banco (janela de 30 dias)
- Transações duplicadas DEVEM ser descartadas silenciosamente (log apenas)

### FR-004-05: Publicação para Conciliação
- Após captura e deduplicação, transações novas DEVEM ser publicadas no broker como evento `TRANSACOES_CAPTURADAS`
- O payload DEVE conter a lista de transações normalizadas e o `consentimento_id`
- O Módulo 008 é o consumidor desse evento

### FR-004-06: Consulta Manual de Extrato
- O MS DEVE oferecer endpoint para captura on-demand de um consentimento específico
- Útil para reconciliação manual acionada pelo usuário no Módulo 008

### FR-004-07: Suporte a Múltiplos Providers
- **Pluggy** *(v1)*: Agregador que suporta +200 bancos brasileiros (OAuth simplificado) — escopo do primeiro release
- **Belvo** *(v1)*: Alternativa ao Pluggy para bancos não cobertos — escopo do primeiro release
- **API Direta Open Finance Brasil** *[FUTURO v2]*: Adapter nativo para bancos que expõem API Open Finance Brasil (Itaú, Bradesco, BB, etc.) diretamente, sem intermediário. Requer certificado digital próprio, registro no Diretório BACEN e fluxo OAuth mais complexo. Será implementado apenas após Pluggy/Belvo estarem estáveis em produção.
- O MS DEVE abstrair a diferença entre providers via padrão de Adapter para que v2 seja adicionável sem alterar o contrato de API

---

## User Stories

### US-004-01: Conectar Conta Bancária
**Como** gerente financeiro da empresa,
**Quando** acesso o Módulo 008 pela primeira vez,
**Quero** autorizar o acesso à minha conta bancária via Open Finance,
**Para que** os extratos sejam capturados automaticamente sem precisar importar arquivos OFX/CNAB manualmente.

**Critérios de Aceite:**
- URL de autorização OAuth gerada em < 2 segundos
- Após callback, token armazenado e primeira captura de extrato iniciada em < 1 minuto
- Evento `CONSENTIMENTO_ATIVO` publicado

### US-004-02: Captura Automática sem Intervenção
**Como** sistema do ERP (Módulo 008),
**A cada 4 horas**,
**Quero** receber automaticamente as novas transações de todas as contas bancárias conectadas,
**Para que** a conciliação seja executada de forma contínua sem ação manual.

**Critérios de Aceite:**
- Cron job executa às 00h, 04h, 08h, 12h, 16h e 20h
- Transações publicadas no broker em lote por consentimento
- Log de captura registrado com status, quantidade e período

### US-004-03: Renovação de Consentimento Expirado
**Como** gerente financeiro,
**Quando** meu consentimento bancário expira (prazo legal de 12 meses),
**Quero** ser notificado com antecedência de 7 dias,
**Para que** eu possa renovar o acesso pelo aplicativo do banco antes de perder a integração.

**Critérios de Aceite:**
- Evento `CONSENTIMENTO_EXPIRANDO` publicado 7 dias antes do vencimento
- Após expiração, evento `CONSENTIMENTO_EXPIRADO` publicado
- Capturas suspensas para o consentimento expirado (sem erros silenciosos)

### US-004-04: Captura Manual por Período
**Como** analista financeiro,
**Quando** percebo que um período específico não foi conciliado,
**Quero** acionar a captura manual de um extrato definindo o período,
**Para que** as transações faltantes sejam importadas e a conciliação seja completada.

**Critérios de Aceite:**
- Captura manual retorna resultado em < 30 segundos para períodos de até 90 dias
- Transações deduplicadas (sem duplicar as já existentes)
- Quantidade de novas transações retornada na resposta

---

## Eventos

### Eventos que o MS-004 **PUBLICA**:

| Evento | Consumido por | Descrição |
|---|---|---|
| `TRANSACOES_CAPTURADAS` | Módulo 008 | Lote de transações normalizadas novas |
| `CONSENTIMENTO_ATIVO` | Módulo 008 | Novo consentimento autorizado com sucesso |
| `CONSENTIMENTO_EXPIRANDO` | Módulo 008, MS-003 | Consentimento a vencer em 7 dias |
| `CONSENTIMENTO_EXPIRADO` | Módulo 008 | Consentimento revogado ou expirado |
| `CAPTURA_ERRO` | Módulo 008 | Falha na captura de um consentimento |

### Eventos que o MS-004 **ESCUTA** (opcionais):

| Evento | Publicado por | Ação |
|---|---|---|
| `EXTRATO_CAPTURAR_MANUAL` | Módulo 008 | Aciona captura on-demand de um consentimento |

---

## API Endpoints (REST Interno)

| Método | Endpoint | Descrição |
|---|---|---|
| `GET` | `/api/v1/oauth/authorize/{banco}` | Gera URL de autorização OAuth para o banco |
| `GET` | `/api/v1/oauth/callback` | Recebe callback OAuth e salva consentimento |
| `GET` | `/api/v1/consentimentos` | Lista consentimentos ativos da empresa |
| `DELETE` | `/api/v1/consentimentos/{id}` | Revoga consentimento |
| `POST` | `/api/v1/extratos/capturar/{consentimento_id}` | Captura on-demand de extrato |
| `GET` | `/api/v1/transacoes` | Lista transações capturadas (com filtros) |
| `GET` | `/api/v1/captura/logs` | Histórico de capturas (status, quantidade) |
| `GET` | `/api/v1/health` | Health check |

---

## Edge Cases

- **Expiração de consentimento**: Refresh token inválido → marcar como `expirado` → publicar `CONSENTIMENTO_EXPIRADO` → suspender capturas → notificar usuário (via MS-003) para renovar no App Bancário
- **Banco indisponível durante captura**: Retry com backoff exponencial (3 tentativas: 30s, 2min, 10min). Após falhar, registrar log de `CAPTURA_ERRO` e continuar com os demais consentimentos
- **Transação duplicada**: Detectada pelo `deduplicacao_hash` → descartada silenciosamente → contabilizada no log de captura como "duplicata ignorada"
- **Transação com valor zero**: Não publicar para conciliação (geralmente são reservas ou tarifas ainda não debitadas)
- **Mudança de layout da API do banco**: Provider desatualizado → captura retorna erro de parsing → publicar `CAPTURA_ERRO` com detalhe do campo problemático para que o adapter seja atualizado
- **Múltiplas capturas simultâneas do mesmo consentimento**: Lock distribuído via Redis para evitar duplicação por condição de corrida no cron

---

## Success Criteria

- **SC-004-01**: Extratos capturados e publicados em < 2 minutos após início do cron job para 100% dos consentimentos ativos
- **SC-004-02**: Zero transações duplicadas em banco (garantido por deduplicação por hash)
- **SC-004-03**: Alerta de consentimento expirando chega ao usuário com ≥ 7 dias de antecedência
- **SC-004-04**: Falha de um banco não impede captura dos demais (isolamento por consentimento)
- **SC-004-05**: 100% dos tokens são armazenados apenas criptografados (nunca em texto plano nos logs)
- **SC-004-06**: Captura manual retorna resultado em < 30 segundos para períodos de até 90 dias
