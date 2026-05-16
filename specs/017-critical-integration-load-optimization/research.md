# Research: Módulo 017 - Critical Integration Load Optimization

## Decisions

- **Decision**: O módulo registrará cenários de carga e execuções de benchmark no ERP central, enquanto ferramentas externas continuam sendo fontes auxiliares de medição bruta.
  - **Rationale**: A governança operacional precisa concentrar baseline, gargalo, tuning e rollback em um ponto auditável reutilizável pela plataforma.
  - **Alternatives considered**:
    - Confiar apenas em relatórios externos de carga: rejeitado por baixa rastreabilidade no contexto do produto.
    - Tratar benchmark como apêndice do módulo `015`: rejeitado porque mistura snapshot operacional contínuo com benchmark controlado e tuning lifecycle.

- **Decision**: Gargalos serão classificados por categoria explícita (`database`, `queue`, `external_endpoint`, `application`).
  - **Rationale**: A operação precisa distinguir a natureza do gargalo antes de aprovar tuning ou rollback.
  - **Alternatives considered**:
    - Classificação livre em texto: rejeitada por dificultar inspeção e comparação automatizada.

- **Decision**: A promoção de baseline exigirá cenário reproduzível e benchmark validado no mesmo ambiente.
  - **Rationale**: Comparar ambientes ou cenários parcialmente diferentes gera ruído operacional e falsos ganhos.
  - **Alternatives considered**:
    - Promover qualquer execução manualmente: rejeitado por aumentar risco de baseline inconsistente.

- **Decision**: Regressão de tuning publicará evento material no backbone `010` e manterá recomendação de rollback auditável.
  - **Rationale**: Ajustes regressivos precisam ser visíveis para operação e suporte, não apenas para o executor técnico.
  - **Alternatives considered**:
    - Apenas log interno da mudança: rejeitado por pouca visibilidade operacional.
