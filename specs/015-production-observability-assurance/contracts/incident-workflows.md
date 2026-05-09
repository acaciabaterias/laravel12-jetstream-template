# Contract: Incident Workflows

## Purpose

Definir os fluxos operacionais mínimos do módulo `015` para alerta, carga, replay, rollback e encerramento de incidente.

## Core workflows

### 1. Classificação de degradação operacional

- coletar sinais de backlog, latência, falha e replay
- comparar contra SLOs e limiares definidos
- persistir snapshot operacional com severidade calculada
- abrir incidente quando a degradação ultrapassar condição material

### 2. Comparação de baseline de carga

- selecionar cenário crítico
- executar ou registrar resultado do teste de carga
- comparar throughput, latência e falha com baseline aceito
- marcar regressão quando o desvio ultrapassar a faixa definida

### 3. Execução de runbook operacional

- selecionar incidente ou fluxo degradado
- executar replay, contingência, rollback ou restore validation
- registrar evidência mínima de início, resultado e validação posterior
- encerrar incidente apenas após rechecagem dos sinais críticos

### 4. Recuperação de serviço

- confirmar normalização dos sinais operacionais
- persistir recuperação com vínculo ao incidente
- publicar evento material de recuperação quando aplicável

## Operational guardrails

- coletor indisponível não pode ser interpretado como estado saudável
- rollback sem validação posterior não encerra incidente
- evidência de runbook é obrigatória para ações críticas
- carga precisa registrar contexto de ambiente para comparação honesta
