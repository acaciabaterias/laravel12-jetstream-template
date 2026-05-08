# Research: Módulo 013 - Platform Revenue Recovery

## Decision 1: Manter recuperação de receita no banco central

**Decision**: Políticas, casos, ações, promessas e métricas de recuperação devem viver exclusivamente no banco central.

**Rationale**: A recuperação de receita pertence à plataforma e depende diretamente de `AssinaturaPlataforma`, `FaturaSaaS`, sinais do `012` e regras comerciais do `011`. Replicar isso em tenants criaria duplicidade e risco de divergência.

**Alternatives considered**:
- Espalhar ações de cobrança nos bancos tenant: descartado por acoplamento indevido.
- Tratar recuperação apenas via ferramentas externas: descartado por perda de governança e auditoria.

## Decision 2: Usar caso operacional explícito por obrigação elegível

**Decision**: Cada obrigação elegível à recuperação deve abrir ou reutilizar um caso operacional central com histórico próprio.

**Rationale**: O caso é a unidade correta para agrupar ações automáticas, escalonamentos e compromissos, sem depender apenas do estado instantâneo da fatura.

**Alternatives considered**:
- Registrar apenas ações isoladas sem caso agregador: descartado por dificultar deduplicação e leitura operacional.

## Decision 3: Deduplicar por estágio, canal e obrigação

**Decision**: Ações automáticas devem ser deduplicadas pela combinação de obrigação financeira, estágio da régua e canal de contato.

**Rationale**: O risco principal deste módulo é bombardear o assinante com contatos repetidos ou conflitantes após retries, falhas de fila ou reavaliações frequentes.

**Alternatives considered**:
- Deduplicar só pela fatura: descartado porque impede evolução legítima entre estágios.
- Confiar apenas na fila: descartado por não cobrir replay e reprocessamento manual.

## Decision 4: Promessa de pagamento suspende ações incompatíveis, não o caso inteiro

**Decision**: Quando houver promessa válida, o caso continua monitorado, mas as ações incompatíveis com o compromisso ficam suspensas até a data acordada.

**Rationale**: Isso preserva observabilidade e responsabilidade operacional sem desrespeitar o combinado com o assinante.

**Alternatives considered**:
- Encerrar o caso imediatamente após promessa: descartado por abrir espaço para esquecimento operacional.
- Ignorar a promessa na régua: descartado por gerar contato indevido.

## Decision 5: Escalonamento deve considerar reincidência e severidade financeira

**Decision**: Escalonamento automático deve usar atraso, valor exposto, reincidência, falhas repetidas e exceções abertas como sinais de criticidade.

**Rationale**: Nem toda inadimplência precisa de intervenção humana imediata; os casos críticos precisam ser priorizados com critério claro.

**Alternatives considered**:
- Escalar tudo manualmente: descartado por custo operacional.
- Escalar apenas por dias de atraso: descartado por perder contexto de recorrência e falhas de gateway.

## Decision 6: Reengajamento deve ser separado da cobrança inicial

**Decision**: Reengajamento após regularização deve ser modelado como ação distinta das etapas de cobrança.

**Rationale**: A lógica de retenção e redução de churn não é a mesma da cobrança. Misturar ambas degrada análise de eficácia por objetivo.

**Alternatives considered**:
- Reusar o mesmo tipo de ação da régua: descartado por confundir recuperação com retenção.

## Decision 7: Backbone 010 será o canal de eventos de recuperação

**Decision**: Abertura de caso, escalonamento crítico, promessa registrada, recuperação confirmada e reengajamento relevante devem gerar eventos centrais no backbone `010`.

**Rationale**: O backbone já fornece replay, observabilidade e contrato versionado para sinais operacionais transversais.

**Alternatives considered**:
- Notificar apenas localmente no módulo: descartado por reduzir rastreabilidade e desacoplamento.

## Decision 8: Rollback deve distinguir comunicação, compromisso e estado financeiro

**Decision**: O runbook do módulo deve distinguir replay de comunicação falha, reversão de compromisso operacional e reabertura por evento financeiro novo.

**Rationale**: Nem toda correção deve restaurar banco ou reenviar mensagens; algumas exigem apenas replanejamento do caso.

**Alternatives considered**:
- Tratar toda inconsistência com restore do banco central: descartado por risco de apagar histórico legítimo de contato e recuperação.
