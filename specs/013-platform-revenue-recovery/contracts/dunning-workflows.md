# Contract: Dunning Workflows

## Purpose

Definir os fluxos operacionais mínimos da régua de cobrança e recuperação de receita do módulo `013`.

## Core workflows

### 1. Abertura do caso de recuperação

- detectar fatura em atraso, falha de cobrança ou reabertura por chargeback
- localizar política ativa aplicável
- abrir ou reutilizar caso operacional rastreável
- registrar estágio inicial, severidade e próximo prazo

### 2. Execução de ação automatizada

- selecionar ação elegível pelo estágio atual
- revalidar status financeiro e comercial antes do envio
- deduplicar por obrigação, estágio e canal
- executar contato ou tarefa operacional
- registrar resultado e próxima etapa

### 3. Escalonamento humano

- identificar caso crítico por atraso, reincidência, valor exposto ou falha repetida
- atribuir responsável operacional
- registrar prioridade, justificativa e prazo de retorno
- impedir que automações incompatíveis rodem em paralelo sem reavaliação

### 4. Promessa de pagamento

- registrar compromisso manual com data e valor prometidos
- suspender somente ações incompatíveis até a janela acordada
- monitorar cumprimento ou quebra da promessa
- reabrir estágio adequado se o compromisso não for honrado

### 5. Recuperação e reengajamento

- receber sinal financeiro de regularização
- encerrar ou pausar a régua ativa
- registrar origem da recuperação
- abrir ação opcional de reengajamento ou retenção quando aplicável

## Operational guardrails

- nenhuma ação crítica deve ocorrer sem autenticação de operador autorizado quando houver intervenção humana
- ações automáticas devem ser idempotentes e deduplicadas por estágio e canal
- regularização financeira deve prevalecer sobre ação de cobrança ainda pendente
- replay de contato falho não pode gerar duplicidade de histórico confirmado como entregue
- compromissos manuais e escalonamentos precisam de trilha auditável
