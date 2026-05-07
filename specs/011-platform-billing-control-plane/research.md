# Research: Módulo 011 - Platform Billing Control Plane

## Decision 1: Manter o billing control plane no banco central

**Decision**: Toda a governança comercial do assinante deve residir exclusivamente no banco central.

**Rationale**: Planos, assinaturas, cobrança SaaS e políticas de bloqueio são responsabilidades da plataforma, não do tenant. Centralizar evita inconsistência entre bases e simplifica auditoria comercial.

**Alternatives considered**:
- Replicar estado comercial em cada tenant: descartado por duplicidade e risco de divergência.
- Guardar apenas flags mínimas em `clientes`: descartado por insuficiência histórica e analítica.

## Decision 2: Separar cobrança SaaS de financeiro operacional do tenant

**Decision**: O módulo `011` governa cobrança da plataforma; o módulo `008` continua responsável por finanças operacionais do negócio do assinante.

**Rationale**: Misturar cobrança SaaS com financeiro do tenant confundiria propriedade de dados, conciliação e escopo funcional.

**Alternatives considered**:
- Reusar tabelas e modelos do `008`: descartado porque são tenant-aware e representam o negócio do assinante, não o da plataforma.

## Decision 3: Política de inadimplência configurável e auditável

**Decision**: Grace period, bloqueio elegível, bloqueio efetivo, reativação e cancelamento precisam ser estados explícitos e auditáveis.

**Rationale**: O sistema já passou a bloquear acesso por inadimplência; agora a origem dessa decisão precisa ser governável, reversível e verificável.

**Alternatives considered**:
- Regra fixa embutida no middleware: descartada por baixa governança e pouca transparência operacional.
- Bloqueio manual sem política: descartado por risco de arbitrariedade e falha humana.

## Decision 4: Painel central orientado por carteira e risco

**Decision**: O painel do super admin deve priorizar saúde comercial da base, carteira vencida, assinantes em grace period, bloqueios e reativações recentes.

**Rationale**: O objetivo do módulo não é só cadastro contratual; ele precisa dar capacidade operacional de cobrança e retenção.

**Alternatives considered**:
- Painel apenas cadastral: descartado por baixo valor operacional.
- Relatórios off-line sem tela viva: descartado por menor capacidade de ação imediata.

## Decision 5: Eventos comerciais mínimos publicados no backbone `010`

**Decision**: O módulo deve publicar apenas eventos operacionais centrais como assinatura ativada, bloqueio aplicado, desbloqueio confirmado, plano alterado e cancelamento efetivado.

**Rationale**: Isso alimenta notificações, observabilidade e integrações futuras sem reabrir todo o fluxo comercial em acoplamento forte.

**Alternatives considered**:
- Não publicar eventos: descartado por perda de rastreabilidade e automação.
- Publicar todos os detalhes internos da cobrança: descartado por excesso de ruído e acoplamento sem ganho proporcional.

## Decision 6: Backup e rollback precisam tratar reversão de estado comercial

**Decision**: Mudanças críticas como bloqueio, reativação, troca de plano e cancelamento devem ter trilha e caminho explícito de reversão.

**Rationale**: Diferente de uma simples alteração cadastral, esse módulo impacta diretamente a continuidade operacional do assinante.

**Alternatives considered**:
- Confiar apenas em auditoria sem caminho de reversão: descartado por não atender a exigência constitucional de resiliência operacional.
