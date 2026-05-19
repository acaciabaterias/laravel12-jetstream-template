# Research: Módulo 023 - Fiscal CFOP Export/Import

## Decision 1: Tratar o catálogo fiscal como governança central separada da emissão

**Rationale**: O módulo `009` já concentra a orquestração fiscal transacional. O `023` deve entregar um catálogo governado e reutilizável sem reescrever a pipeline de emissão, reduzindo risco de regressão.

**Alternatives considered**:
- Alterar diretamente a emissão fiscal do módulo `009`: rejeitado por ampliar escopo e risco operacional.
- Manter regras fiscais fora do ERP em planilhas: rejeitado por perder rastreabilidade e rollback.

## Decision 2: Versionar publicações de CFOP e cenários como pacote único

**Rationale**: Exportação/importação dependem de coerência entre catálogo, direção fiscal e cenário obrigatório. Publicar um pacote único facilita inspeção, promoção e reversão.

**Alternatives considered**:
- Versionar cada CFOP isoladamente: rejeitado por dificultar leitura consistente do cenário ativo.
- Atualizar tabela em linha sem publicação: rejeitado por não preservar histórico e evidência de mudança.

## Decision 3: Medir cobertura mínima por conjunto governado de cenários fiscais obrigatórios

**Rationale**: O valor operacional do módulo está em garantir que cenários críticos de exportação/importação não fiquem sem enquadramento explícito. A cobertura mínima por cenário entrega critério objetivo de prontidão.

**Alternatives considered**:
- Publicar qualquer conjunto parcial de regras: rejeitado por permitir lacunas silenciosas.
- Validar só no frontend: rejeitado por reduzir auditabilidade e reaproveitamento.

## Decision 4: Publicar eventos materiais no backbone `010`

**Rationale**: Mudanças fiscais impactam suporte, observabilidade, relatórios e readiness do fluxo de emissão. Eventos materiais preservam visibilidade cross-module.

**Alternatives considered**:
- Não publicar eventos: rejeitado por enfraquecer a trilha operacional.
- Publicar apenas logs locais: rejeitado por não integrar com a governança já consolidada do backbone.
