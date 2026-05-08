# Research: Módulo 014 - Platform Commercial Analytics

## Decision 1: Analytics comercial continua no banco central

**Decision**: Snapshots, recortes e drill-downs analíticos devem ser mantidos exclusivamente no banco central.

**Rationale**: Os módulos `011`, `012` e `013` já estabilizaram a fonte de verdade comercial, financeira e de recuperação no plano central. Levar analytics para tenants só adicionaria ruído e duplicação.

**Alternatives considered**:
- Montar analytics diretamente em tenants: descartado por fragmentação e baixa governança.
- Delegar toda a análise a ferramenta externa sem persistência central: descartado por perda de rastreabilidade e dependência operacional.

## Decision 2: Snapshot analítico é derivado, não fonte de verdade

**Decision**: Snapshots analíticos devem ser reconstruíveis a partir das entidades centrais e nunca se tornar o dado canônico da operação.

**Rationale**: Correções em billing, payments ou recovery precisam refletir analytics sem exigir manutenção manual paralela.

**Alternatives considered**:
- Tratar o snapshot como dado final imutável: descartado por rigidez excessiva diante de correções operacionais.

## Decision 3: MRR, churn e recuperação precisam de regras explícitas de contagem

**Decision**: O módulo deve formalizar regras centrais para evitar dupla contagem de cancelamento, reativação, recuperação e inadimplência no mesmo ciclo.

**Rationale**: Métrica executiva perde credibilidade imediatamente quando diverge do comportamento real das assinaturas ou faturas.

**Alternatives considered**:
- Somar estados atuais sem regra temporal: descartado por distorcer tendências.

## Decision 4: Coorte mínima deve nascer da entrada do assinante

**Decision**: A segmentação inicial por coorte deve se basear na entrada/ativação da assinatura e ser complementada por canal e carteira.

**Rationale**: Isso permite comparar retenção e recuperação ao longo do tempo com referência consistente.

**Alternatives considered**:
- Criar coortes só por mês de faturamento: descartado por enfraquecer leitura de retenção e ramp-up comercial.

## Decision 5: Drill-down é requisito funcional, não extra opcional

**Decision**: Todo indicador executivo relevante deve ter caminho claro até clientes, assinaturas, faturas ou casos que o compõem.

**Rationale**: A utilidade do painel depende da capacidade de sair do agregado e chegar ao caso acionável.

**Alternatives considered**:
- Mostrar apenas cards executivos: descartado por transformar analytics em painel passivo.

## Decision 6: Rebuild analítico deve ser controlado e auditável

**Decision**: Recalcular snapshots precisa de serviço/job/comando controlado, com trilha mínima de execução e impacto previsível.

**Rationale**: Rebuild será necessário após correções operacionais, e não pode gerar dupla contagem silenciosa.

**Alternatives considered**:
- Atualização ad hoc e manual em consultas SQL: descartado por baixa reprodutibilidade.

## Decision 7: Backbone 010 deve receber eventos analíticos materiais

**Decision**: Atualizações relevantes de snapshot e insights operacionais críticos devem ser publicadas no backbone `010`.

**Rationale**: O backbone já oferece rastreabilidade e desacoplamento para sinais transversais da plataforma.

**Alternatives considered**:
- Não publicar nada do analytics: descartado por reduzir capacidade de observabilidade e integração.

## Decision 8: Rollback deve focar reconstrução, não restauração cega

**Decision**: O runbook do módulo deve priorizar rebuild e revalidação dos snapshots antes de considerar restore integral.

**Rationale**: Como analytics é derivado, o caminho correto quase sempre será reconstruir a partir da fonte operacional central.

**Alternatives considered**:
- Restaurar snapshot antigo sempre que houver divergência: descartado por risco de esconder correções reais da base operacional.
