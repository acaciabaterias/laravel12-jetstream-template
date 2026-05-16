# Research: Módulo 019 - Executive Reporting Hub

## Decision 1: Reusar a base analítica central do módulo 014 como origem do hub executivo

**Rationale**: O módulo `014` já consolidou indicadores comerciais centrais. O `019` deve ampliar consumo, filtros e apresentação, não duplicar a camada de cálculo. Isso reduz divergência entre dashboards e preserva a fonte única de verdade.

**Alternatives considered**:
- Recalcular tudo do zero em uma nova camada executiva: rejeitado por aumentar inconsistência e custo de manutenção.
- Exportar direto de consultas ad hoc por relatório: rejeitado por fragilizar auditabilidade e repetibilidade.

## Decision 2: Persistir contexto de exportação e histórico de execução, não apenas o artefato final

**Rationale**: O valor operacional do módulo está na capacidade de reinspecionar e reexecutar relatórios sem remontagem manual. Isso exige salvar filtros, período, operador, formato e estado da execução, além do arquivo gerado.

**Alternatives considered**:
- Persistir só o caminho do arquivo: rejeitado por não permitir reexecução confiável nem trilha de contexto.
- Gerar relatório sob demanda sem histórico: rejeitado por contrariar governança e auditoria.

## Decision 3: Tratar PDF e Excel como projeções do mesmo snapshot executivo

**Rationale**: O dashboard, o Excel e o PDF devem refletir o mesmo recorte analítico. Modelar ambos como projeções do mesmo snapshot reduz risco de divergência material entre formatos.

**Alternatives considered**:
- Gerar PDF e Excel com pipelines independentes: rejeitado por elevar risco de números inconsistentes.
- Limitar o módulo a um único formato: rejeitado por não cobrir a necessidade executiva declarada no roadmap.

## Decision 4: Exigir filtros explícitos e escopo reproduzível para exportação

**Rationale**: Relatórios executivos amplos demais ou ambíguos perdem utilidade. O módulo deve registrar claramente período, recortes e universo analisado para que o relatório seja compreensível e reexecutável.

**Alternatives considered**:
- Permitir exportações sem escopo explícito: rejeitado por produzir material difícil de auditar.
- Forçar templates rígidos sem filtros: rejeitado por reduzir demais a utilidade analítica do hub.
