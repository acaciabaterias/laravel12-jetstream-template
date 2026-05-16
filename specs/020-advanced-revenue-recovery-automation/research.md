# Research: Módulo 020 - Advanced Revenue Recovery Automation

## Decision 1: A automação avançada deve operar por versão publicada de política

**Decision**: Cada jornada automática deve referenciar uma `RecoveryAutomationPolicyVersion` imutável após publicação, com uma única versão ativa por escopo controlado.

**Rationale**: Isso permite comparar performance, isolar regressões e executar rollback sem ambiguidade sobre qual estratégia tratou cada caso.

**Alternatives considered**:

- Editar a política ativa diretamente: descartado por perder comparabilidade e trilha de rollback.
- Reusar apenas a política base do módulo `013`: descartado por não cobrir variações publicáveis nem escopo experimental.

## Decision 2: Fallback e supressão devem ser avaliados antes de todo dispatch

**Decision**: O mecanismo de automação deve revalidar segmento, promessa, janela, cooldown, supressão e fallback imediatamente antes do dispatch.

**Rationale**: A elegibilidade pode mudar entre o agendamento e a execução. Revalidar evita contato indevido e reduz dispatches desperdiçados.

**Alternatives considered**:

- Validar apenas no momento do agendamento: descartado por permitir envio com contexto já vencido.
- Pausar toda a automação na presença de promessa ou canal falho: descartado por sacrificar flexibilidade operacional.

## Decision 3: Experimentos precisam de holdout explícito e associação persistida por caso

**Decision**: Variantes experimentais e grupos de controle devem ser atribuídos no início da jornada automatizada e preservados até o encerramento ou rollback.

**Rationale**: Atribuição persistida impede que um mesmo caso mude de variante ao longo do fluxo e garante leitura comparável de performance.

**Alternatives considered**:

- Sortear variante a cada etapa: descartado por invalidar comparação entre estratégias.
- Medir apenas depois da regularização: descartado por esconder backlog e violações intermediárias.

## Decision 4: Rollback deve trocar a política ativa sem reescrever histórico executado

**Decision**: O rollback deve restaurar a última política saudável para novos avanços e marcar as jornadas afetadas, preservando dispatches, violações e experimentos já registrados.

**Rationale**: Reescrever histórico destruiria a rastreabilidade comercial e operacional exigida para auditoria.

**Alternatives considered**:

- Apagar ou migrar dispatches da política revertida: descartado por apagar evidência.
- Reabrir todos os casos automaticamente em outra política: descartado por aumentar risco de duplicidade.
