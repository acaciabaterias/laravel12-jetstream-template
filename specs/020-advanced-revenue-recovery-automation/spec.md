# Feature Specification: Módulo 020 - Advanced Revenue Recovery Automation

**Feature Branch**: `020-advanced-revenue-recovery-automation`  
**Created**: 2026-05-16  
**Status**: Draft  
**Input**: User description: "Dar continuidade aos módulos restantes após o executive reporting, começando pela automação avançada de cobrança e recuperação de receita."

## Contexto

O módulo `013` estabeleceu a régua central de recuperação de receita com casos, ações, promessas e escalonamentos auditáveis. O módulo `019` passou a expor visibilidade executiva consolidada desses resultados. O próximo gap está na profundidade da automação: a plataforma já reage a inadimplência, mas ainda depende de políticas relativamente estáticas, pouca orquestração por segmento e intervenção manual excessiva para experimentar ou reverter estratégias de cobrança.

Este módulo define a automação avançada de recuperação. Ele deve versionar políticas automáticas, orquestrar jornadas com fallback entre canais, aplicar supressão e cooldown, operar experimentos controlados e permitir rollback seguro quando uma estratégia degradar recuperação, elevar reclamações ou gerar comunicação indevida.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | A automação continua operando somente no plano central e nunca desloca workflow comercial para bancos tenant |
| Automated Financial Microservices | Aprofunda a camada automatizada de cobrança usando backbone, billing, payments e canais externos sem planilhas ou cadências paralelas |
| Operational Resilience & Disaster Recovery | Exige rollback auditável de políticas, replay controlado de disparos e reconstrução consistente da jornada ativa |
| Product Governance | Toda alteração de estratégia deve ficar versionada, comparável e publicável com guardrails explícitos |

## User Scenarios & Testing

### User Story 1 - Orquestrar jornadas automáticas adaptativas (Priority: P1)

Como operador da plataforma, quero que casos elegíveis avancem automaticamente pela melhor próxima ação de cobrança com fallback, cooldown e supressão compatíveis com o contexto do assinante, para reduzir atraso operacional e contato indevido.

**Why this priority**: Sem a automação adaptativa, a régua central continua útil, porém limitada por passos estáticos e baixa capacidade de escalar carteiras maiores com segurança.

**Independent Test**: Pode ser validado simulando um caso de recuperação elegível com diferentes sinais de canal, promessa e recorrência, confirmando que o sistema agenda apenas a próxima ação válida e escolhe fallback quando o canal principal estiver indisponível.

**Acceptance Scenarios**:

1. **Given** um caso de recuperação elegível para avanço automático, **When** a jornada automatizada for avaliada, **Then** o sistema deve selecionar a próxima ação compatível com segmento, janela, canal e cooldown vigentes.
2. **Given** um canal prioritário temporariamente inelegível ou indisponível, **When** a automação precisar disparar a próxima etapa, **Then** o sistema deve usar o fallback permitido sem duplicar ação já aberta ou violar supressão ativa.

---

### User Story 2 - Publicar estratégias automáticas com governança e experimento (Priority: P2)

Como gestor comercial da plataforma, quero publicar versões de estratégia automatizada e testá-las por segmento ou holdout controlado, para evoluir cobrança sem arriscar toda a carteira de uma vez.

**Why this priority**: Depois da automação básica, o ganho real vem de testar e promover estratégias melhores com trilha governada, não de alterar regras diretamente em produção sem comparação.

**Independent Test**: Pode ser validado criando uma nova versão de política, aprovando sua publicação para um segmento controlado e confirmando que apenas os casos elegíveis entram no experimento ou holdout esperado.

**Acceptance Scenarios**:

1. **Given** uma nova versão de política de automação pronta para uso, **When** o gestor publicar a estratégia com escopo controlado, **Then** o sistema deve ativá-la somente para os segmentos definidos e preservar a versão anterior como fallback reversível.
2. **Given** um experimento com grupo de controle ou holdout configurado, **When** novos casos elegíveis entrarem na jornada, **Then** o sistema deve respeitar a distribuição prevista e registrar em qual estratégia cada caso foi tratado.

---

### User Story 3 - Inspecionar performance e reverter automações degradadas (Priority: P3)

Como super admin, quero inspecionar performance por versão automatizada e executar rollback ou reprocessamento quando uma estratégia degradar recuperação ou gerar violações, para manter governança operacional da cobrança.

**Why this priority**: Automação avançada sem inspeção, rollback e evidência comparável vira risco sistêmico de comunicação indevida ou perda de receita em escala.

**Independent Test**: Pode ser validado simulando uma política degradada ou com violações, verificando que o painel central sinaliza a anomalia e permite rollback auditável para a última versão saudável.

**Acceptance Scenarios**:

1. **Given** uma estratégia automatizada com degradação material de conversão ou aumento de violações, **When** a plataforma avaliar a performance da versão ativa, **Then** o sistema deve sinalizar a necessidade de intervenção e expor a comparação contra a versão de referência.
2. **Given** uma versão ativa considerada insegura ou ineficaz, **When** o super admin executar rollback controlado, **Then** o sistema deve restaurar a versão anterior elegível e registrar evidência auditável da reversão e dos casos afetados.

## Edge Cases

- Um mesmo caso não pode receber duas ações automáticas equivalentes na mesma janela operacional por erro de reprocessamento, retry ou corrida de jobs.
- Uma promessa de pagamento, holdout comercial ou bloqueio manual incompatível deve impedir avanço automático sem apagar observabilidade do caso.
- Falha repetida de um canal não pode forçar fallback infinito ou escalada de contatos além do limite permitido pela política publicada.
- Mudança de status financeiro entre agendamento e execução deve cancelar ou reavaliar a ação antes do disparo real.
- Rollback de estratégia não pode perder associação histórica entre caso, versão da política, experimento e ação já executada.
- Em cenário de backup, restore ou rollback, a plataforma deve reconstruir a última combinação consistente entre política ativa, jornada pendente e dispatch confirmado sem reenviar comunicação já concluída.

## Requirements

### Functional Requirements

- **FR-ARA-001**: O sistema MUST suportar versões explícitas de políticas de automação de recuperação com estado draft, active, superseded e rolled_back.
- **FR-ARA-002**: O sistema MUST avaliar automaticamente a próxima ação elegível de um caso de recuperação considerando estágio atual, segmento, severidade, promessas vigentes, cooldown, supressão e janelas de execução.
- **FR-ARA-003**: O sistema MUST permitir fallback ordenado entre canais ou templates quando o canal prioritário estiver indisponível, bloqueado ou fora da janela publicada.
- **FR-ARA-004**: O sistema MUST impedir dispatch automático duplicado para o mesmo caso, versão de política, estágio e janela operacional sem autorização explícita de replay.
- **FR-ARA-005**: O sistema MUST registrar em cada dispatch automático a política aplicada, a variante experimental, o canal escolhido, o motivo da escolha e o resultado observado.
- **FR-ARA-006**: O sistema MUST permitir publicar políticas com escopo controlado por segmento, carteira, severidade, atraso ou holdout definido.
- **FR-ARA-007**: O sistema MUST suportar experimento controlado entre variantes de automação e grupo de controle com rastreabilidade de qual estratégia tratou cada caso.
- **FR-ARA-008**: O sistema MUST bloquear publicação de estratégia automatizada sem guardrails mínimos de frequência, supressão, fallback e rollback definidos.
- **FR-ARA-009**: O sistema MUST expor painéis e inspeções reutilizáveis com desempenho por política, variante, canal, segmento, violação e rollback executado.
- **FR-ARA-010**: O sistema MUST sinalizar violações materiais de automação, incluindo excesso de contato, dispatch fora da janela, fallback exaurido e degradação relevante de performance.
- **FR-ARA-011**: O sistema MUST permitir rollback controlado para a última política saudável sem perder histórico das jornadas já tratadas pela política revertida.
- **FR-ARA-012**: O sistema MUST permitir reprocessamento ou replay controlado de dispatches falhos preservando idempotência e evidência auditável.
- **FR-ARA-013**: O sistema MUST publicar eventos materiais da automação avançada no backbone `010` para dispatch, publicação de política, violação e rollback.
- **FR-ARA-014**: O sistema MUST preservar isolamento e governança central para que a automação nunca grave estados comerciais operacionais no banco tenant.
- **FR-ARA-015**: Para esta feature com impacto direto em receita e comunicação em escala, a spec MUST definir requisitos de backup, restore validation e rollback para políticas, jornadas, dispatches e experimentos.

### Key Entities

- **RecoveryAutomationPolicyVersion**: representa uma versão publicável da política automatizada com escopo, guardrails, fallback e estado operacional.
- **RecoveryAutomationJourney**: representa a jornada automatizada atribuída a um caso de recuperação sob uma política específica.
- **RecoveryAutomationDispatch**: representa um disparo automático planejado ou executado, incluindo canal, variante, tentativa e resultado.
- **RecoveryAutomationExperiment**: representa a configuração de experimento ou holdout usada para comparar variantes de automação.
- **RecoveryAutomationViolation**: representa uma violação material detectada durante avaliação, agendamento, execução ou pós-processamento da automação.

## Success Criteria

### Measurable Outcomes

- **SC-ARA-001**: Pelo menos 95% dos casos elegíveis recebem definição de próxima ação automatizada em até 5 minutos após a transição de estágio relevante.
- **SC-ARA-002**: Nenhum caso recebe mais de um dispatch automático equivalente na mesma janela operacional sem replay autorizado.
- **SC-ARA-003**: Gestores conseguem publicar uma nova política controlada ou executar rollback em até 3 interações a partir do painel central.
- **SC-ARA-004**: Toda versão automatizada ativa apresenta comparação auditável de conversão, backlog e violações contra a referência vigente.
- **SC-ARA-005**: Um rollback validado restaura a última política saudável sem perda de trilha histórica de dispatches, experimentos e violações.

## Dependencies

- Módulo `010` para backbone, replay, rastreabilidade e eventos materiais
- Módulo `011` para estados comerciais e saúde do assinante
- Módulo `012` para origem de falhas financeiras, liquidação e exceções
- Módulo `013` para casos, ações, promessas e métricas base de recuperação
- Módulo `019` para leitura executiva consolidada da performance de recuperação
- `MS-003` ou canal equivalente para entrega e observabilidade de contatos automatizados
- Módulo `002` para autorização, auditoria e operação administrativa central
