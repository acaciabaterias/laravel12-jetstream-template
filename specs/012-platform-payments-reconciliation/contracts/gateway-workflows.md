# Contract: Gateway Workflows

## Purpose

Definir os fluxos operacionais mínimos entre a plataforma e o gateway de cobrança do módulo `012`.

## Core workflows

### 1. Emissão de cobrança SaaS

- selecionar `FaturaSaaS` elegível
- escolher gateway e meio de pagamento habilitados
- emitir cobrança com chave idempotente
- registrar identificador externo, vencimento emitido e estado operacional inicial

### 2. Processamento de webhook ou retorno

- receber retorno do provedor
- validar referência operacional mínima
- registrar payload bruto e chave idempotente
- localizar cobrança externa e fatura central correspondentes
- executar baixa automática quando o match for seguro
- abrir exceção operacional quando houver divergência

### 3. Reprocessamento operacional

- selecionar retorno falho ou exceção aberta
- registrar operador e motivo
- replayar processamento mantendo trilha auditável
- evitar duplicidade de baixa ou efeito financeiro

### 4. Estorno, chargeback ou reversão

- registrar o evento externo que reverte ou contesta o recebimento
- preservar a liquidação original na trilha
- abrir exceção com impacto comercial correspondente
- publicar evento financeiro central compatível com o backbone

## Operational guardrails

- nenhum fluxo crítico deve ocorrer sem autenticação de super admin autorizado
- emissão e webhook devem ser idempotentes
- payloads sensíveis não devem ser expostos em telas ou eventos publicados
- divergências que afetem estado comercial devem ser rastreáveis e reversíveis
