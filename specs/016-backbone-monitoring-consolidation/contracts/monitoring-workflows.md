# Contract: Monitoring Workflows

## Purpose

Definir os fluxos operacionais mínimos do módulo `016` para scrape health, versionamento, readiness e rollback do stack externo de monitoramento.

## Core workflows

### 1. Registro de targets monitorados

- cadastrar target central e fluxo crítico correspondente
- validar endpoint, ambiente e tipo de coletor
- tornar o target elegível para scrape health e readiness

### 2. Avaliação de scrape health

- coletar último estado conhecido do target
- classificar disponibilidade, latência e falha de coleta
- persistir snapshot de probe e readiness do monitoramento
- publicar evento material quando o coletor ou target entrar em estado crítico

### 3. Provisão de dashboards e alertas

- registrar pacote de dashboards e regras versionadas
- associar ambiente, versão aplicada e data de validação
- marcar readiness do pacote provisionado

### 4. Rollback do monitoramento

- selecionar pacote e versão anterior válida
- registrar execução do rollback
- revalidar scrape health, alertas e renderização mínima
- persistir evidência posterior e vínculo auditável

## Operational guardrails

- scrape indisponível não pode ser tratado como ausência de alerta
- rollback sem revalidação posterior não encerra readiness pendente
- taxonomia de fluxo deve permanecer compatível com `010` e `015`
- painel externo não pode ser considerado válido sem evidência de provisão aplicada
