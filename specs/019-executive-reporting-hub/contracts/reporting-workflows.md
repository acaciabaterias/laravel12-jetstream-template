# Contract: Executive Reporting Workflows

## Workflow 1: Exploração executiva com drill-down

1. Super admin acessa dashboard central.
2. Aplica filtros executivos válidos.
3. Sistema recalcula snapshot e KPIs.
4. Operação consulta drill-down de indicador relevante.
5. Sistema mantém coerência entre resumo e detalhamento.

## Workflow 2: Exportação validada em Excel/PDF

1. Operação parte de um snapshot executivo consistente.
2. Solicita exportação em `excel` ou `pdf`.
3. Sistema valida escopo, autorização e completude do snapshot.
4. Sistema registra exportação, gera artefato e publica evento material.
5. Histórico passa a refletir operador, filtros e formato gerado.

## Workflow 3: Reexecução auditável

1. Operação acessa histórico de relatórios gerados.
2. Seleciona uma exportação anterior.
3. Sistema recupera contexto original do snapshot e dos filtros.
4. Nova execução é registrada como reexecução auditável.
5. Inspeção central diferencia exportação original e repetição.
