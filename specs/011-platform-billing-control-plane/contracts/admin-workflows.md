# Contract: Admin Workflows

## Purpose

Definir os fluxos administrativos mínimos do super admin para operação do módulo `011`.

## Core workflows

### 1. Ativação de assinatura

- selecionar assinante central
- escolher plano
- definir vigência e primeira cobrança
- confirmar ativação com registro de autor e motivo quando aplicável

### 2. Troca de plano

- selecionar assinatura ativa
- escolher novo plano
- definir data efetiva
- registrar justificativa operacional
- preservar histórico da assinatura anterior

### 3. Aplicação de bloqueio

- identificar elegibilidade por política e faturas vencidas
- confirmar bloqueio efetivo
- registrar autor, data efetiva e motivo
- publicar evento comercial correspondente

### 4. Reativação

- confirmar regularização ou exceção autorizada
- remover estado de bloqueio
- registrar data, autor e base da decisão
- publicar evento comercial correspondente

## Operational guardrails

- nenhum fluxo crítico deve ocorrer sem autenticação de super admin autorizado
- ações críticas devem ficar auditáveis
- filtros por status e período devem estar disponíveis para operação
