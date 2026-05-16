# Contract: Load Workflows

## Purpose

Definir os fluxos operacionais mínimos do módulo `017` para benchmark, gargalo, tuning e rollback de performance.

## Core workflows

### 1. Registro de cenário de carga

- cadastrar cenário de benchmark associado a fluxo crítico e ambiente
- validar orçamento, duração, concorrência e tolerâncias esperadas
- tornar o cenário elegível para baseline e revalidação

### 2. Execução de benchmark

- registrar janela de benchmark
- persistir throughput, latência, erro e classificação comparativa
- associar gargalos observados e evidência operacional
- publicar evento material quando houver regressão relevante

### 3. Registro e validação de tuning

- registrar hipótese e alteração aplicada
- vincular benchmark baseline e benchmark de validação
- classificar ganho, estabilidade ou regressão
- promover baseline validada quando aplicável

### 4. Rollback de performance

- selecionar tuning regressivo e baseline anterior válida
- registrar execução do rollback
- revalidar benchmark mínimo
- persistir evidência posterior e vínculo auditável

## Operational guardrails

- benchmark incompleto não pode ser promovido como baseline
- tuning regressivo sem rollback ou justificativa não encerra o ciclo operacional
- taxonomia de fluxo deve permanecer compatível com `010`, `015` e `016`
- gargalo crítico não pode ser registrado sem categoria e componente associado
