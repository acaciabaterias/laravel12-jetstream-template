# Contract: Executive Workflows

## Purpose

Definir os fluxos operacionais mínimos do módulo `014` para leitura executiva, drill-down e rebuild analítico.

## Core workflows

### 1. Atualização de snapshot executivo

- coletar sinais centrais de billing, payments e recovery
- calcular MRR, churn, inadimplência, recuperação e bloqueios
- persistir snapshot auditável da janela
- publicar evento analítico material quando aplicável

### 2. Segmentação por coorte e canal

- derivar coortes a partir da entrada/ativação das assinaturas
- segmentar resultados por canal de cobrança e recuperação
- persistir recortes para leitura executiva consistente

### 3. Drill-down analítico

- selecionar indicador agregado
- localizar os registros operacionais que compõem o recorte
- devolver lista reutilizável por dashboard e endpoint de inspeção

### 4. Rebuild controlado

- selecionar janela analítica ou snapshot alvo
- reconstruir agregações a partir da base operacional central
- validar que não houve dupla contagem
- registrar resultado e impacto do rebuild

## Operational guardrails

- analytics não pode sobrescrever a fonte operacional de origem
- rebuild não pode gerar dupla contagem silenciosa
- drill-down deve apontar para composição real do indicador
- recortes vazios devem retornar zero explícito
