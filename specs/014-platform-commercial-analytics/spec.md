# Feature Specification: Módulo 014 - Platform Commercial Analytics

**Feature Branch**: `014-platform-commercial-analytics`  
**Created**: 2026-05-08  
**Status**: Draft  
**Input**: User description: "Prosseguir para o próximo módulo do roadmap após revenue recovery, cobrindo analytics comercial da plataforma, métricas executivas, coortes, churn e drill-down para pricing, retenção e cobrança."

## Contexto

Os módulos `011`, `012` e `013` fecharam o plano de controle comercial, financeiro e de recuperação do SaaS. A próxima lacuna não está em operação transacional, e sim em leitura executiva e capacidade analítica: transformar dados comerciais já produzidos em indicadores acionáveis para pricing, retenção, cobrança e crescimento.

Este módulo define a camada central de analytics comercial. Ele deve consolidar MRR, churn, recuperação, inadimplência, performance por canal e coortes de clientes, permitindo que a plataforma identifique tendências, compare carteiras, meça efetividade operacional e tome decisões sem depender de exportações manuais.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | A análise opera sobre dados centrais agregados e nunca mistura bancos tenant como fonte operacional direta |
| Automated Financial Microservices | Reutiliza sinais comerciais, financeiros e de recuperação já publicados pelos módulos `010` a `013` |
| Operational Resilience & Disaster Recovery | Exige reprodutibilidade dos indicadores, snapshots auditáveis e rollback seguro de agregações centrais |
| Development Workflow & Quality Gates | Requer métricas verificáveis, segmentações explícitas e trilha clara entre indicador executivo e origem operacional |

## User Scenarios & Testing

### User Story 1 - Consolidar métricas executivas do SaaS (Priority: P1)

Como super admin, quero visualizar métricas centrais de MRR, churn, recuperação e inadimplência para entender rapidamente a saúde comercial da plataforma sem consolidar dados manualmente.

**Why this priority**: Sem esse painel executivo, os módulos anteriores geram dados ricos, mas a tomada de decisão continua cega ou dependente de extrações externas.

**Independent Test**: Pode ser validado populando assinaturas, faturas, liquidações e casos de recovery e confirmando que o painel central resume MRR, inadimplência, recuperação e churn em uma visão única.

**Acceptance Scenarios**:

1. **Given** uma base com assinaturas ativas, bloqueadas, canceladas e recuperadas, **When** o super admin consultar o painel executivo, **Then** o sistema deve exibir indicadores agregados centrais consistentes com os estados comerciais e financeiros atuais.
2. **Given** alterações recentes em pagamento ou recuperação, **When** o painel for atualizado, **Then** os indicadores devem refletir o novo estado sem exigir exportação ou consolidação manual.

---

### User Story 2 - Analisar coortes, canais e carteiras (Priority: P2)

Como gestor da plataforma, quero comparar desempenho por coorte, canal de cobrança e carteira comercial para identificar onde a retenção, recuperação e inadimplência estão melhores ou piores.

**Why this priority**: Depois da visão executiva geral, o valor real vem da capacidade de localizar as origens do desempenho e priorizar intervenção.

**Independent Test**: Pode ser validado simulando grupos com datas de entrada, canais e resultados distintos e confirmando segmentação analítica clara por coorte, canal e carteira.

**Acceptance Scenarios**:

1. **Given** assinantes em coortes diferentes, **When** o gestor filtrar a análise por período de entrada, **Then** o sistema deve segmentar retenção, churn e recuperação por coorte.
2. **Given** diferentes canais de cobrança e recuperação, **When** o gestor analisar performance por canal, **Then** o sistema deve mostrar conversão, inadimplência e recuperação comparáveis entre eles.

---

### User Story 3 - Explorar drill-down e apoiar decisão comercial (Priority: P3)

Como liderança comercial, quero navegar dos indicadores agregados para subconjuntos específicos de clientes e obrigações para apoiar decisões de pricing, retenção e cobrança com evidência concreta.

**Why this priority**: Analytics sem drill-down vira quadro bonito sem consequência operacional. A liderança precisa chegar do número ao caso e voltar com decisão defensável.

**Independent Test**: Pode ser validado selecionando um indicador crítico e confirmando que o sistema expõe a lista segmentada de clientes, assinaturas ou faturas correspondentes com filtros reaproveitáveis.

**Acceptance Scenarios**:

1. **Given** um indicador de churn elevado em determinada coorte, **When** a liderança abrir o drill-down, **Then** o sistema deve mostrar os clientes e assinaturas que compõem esse recorte.
2. **Given** uma métrica de recuperação abaixo da meta em certo canal, **When** o gestor aprofundar a análise, **Then** o sistema deve listar os casos e a carteira correspondente para investigação e ajuste operacional.

## Edge Cases

- Mudanças tardias de pagamento ou recovery não podem deixar indicadores executivos permanentemente divergentes do estado operacional.
- Cancelamentos e reativações no mesmo ciclo devem ser tratados sem dupla contagem de churn.
- Coortes com volume baixo devem continuar visíveis sem distorcer médias agregadas de forma silenciosa.
- Métricas consolidadas não podem depender de dados ausentes em um único módulo quando houver sinal equivalente nos demais módulos centrais.
- Drill-down de recortes vazios deve retornar zero explícito e não falha ou dado residual.
- Em cenário de backup, restore ou rollback, snapshots analíticos devem poder ser reconstruídos sem duplicar agregações históricas.

## Requirements

### Functional Requirements

- **FR-CA-001**: O sistema MUST consolidar métricas executivas centrais de MRR, churn, inadimplência, recuperação e bloqueio comercial da plataforma.
- **FR-CA-002**: O sistema MUST permitir segmentar indicadores por período, coorte de entrada, canal de cobrança, canal de recuperação e carteira comercial quando esses eixos estiverem disponíveis.
- **FR-CA-003**: O sistema MUST expor tendências mínimas de crescimento, cancelamento, recuperação e atraso em janelas comparáveis.
- **FR-CA-004**: O sistema MUST fornecer drill-down de cada recorte executivo até assinaturas, clientes, faturas ou casos de recovery que compõem o indicador.
- **FR-CA-005**: O sistema MUST evitar dupla contagem de churn, recuperação ou inadimplência em transições que ocorram no mesmo ciclo analítico.
- **FR-CA-006**: O sistema MUST permitir recalcular ou reconstruir agregações analíticas centrais quando houver correção operacional relevante.
- **FR-CA-007**: O sistema MUST manter rastreabilidade entre o indicador executivo e a origem dos dados comerciais, financeiros e de recovery usados no cálculo.
- **FR-CA-008**: O sistema MUST expor visão central reutilizável por dashboard e endpoint de inspeção analítica sem exigir consultas manuais dispersas.
- **FR-CA-009**: O sistema MUST suportar comparações entre canais e coortes sem exigir exportação manual para planilhas externas.
- **FR-CA-010**: O sistema MUST permitir identificar clientes ou carteiras em risco a partir de combinações de churn, atraso, falha de cobrança e recovery incompleto.
- **FR-CA-011**: O sistema MUST publicar eventos analíticos centrais relevantes ao backbone `010` quando houver atualização de snapshot ou geração de insight operacional material.
- **FR-CA-012**: Para esta feature orientada a decisão executiva, a spec MUST definir requisitos de backup, restore validation e rollback para snapshots analíticos, reconstrução e auditoria de agregações.

### Key Entities

- **SnapshotAnalyticsComercial**: representa um snapshot central das métricas executivas da plataforma em determinada janela.
- **RecorteCoorteComercial**: representa uma visão segmentada por data de entrada, período ou carteira de assinantes.
- **MetricChannelPerformance**: representa indicadores comparativos por canal de cobrança ou recuperação.
- **InsightRiscoComercial**: representa um agrupamento de clientes ou assinaturas sinalizados por risco comercial ou financeiro.
- **DrilldownAnalyticsComercial**: representa a ligação entre métrica agregada e os registros operacionais que a compõem.

## Success Criteria

### Measurable Outcomes

- **SC-CA-001**: Liderança consegue visualizar MRR, churn, inadimplência e recuperação em um único painel sem exportação manual.
- **SC-CA-002**: Indicadores segmentados por coorte e canal podem ser obtidos em até 3 interações a partir do painel executivo.
- **SC-CA-003**: Drill-down de um indicador executivo expõe a composição operacional correspondente sem divergência perceptível com os módulos `011` a `013`.
- **SC-CA-004**: Reconstrução de snapshots analíticos não gera dupla contagem de métricas históricas.
- **SC-CA-005**: Gestores conseguem isolar ao menos um recorte crítico de churn, recuperação ou inadimplência sem recorrer a planilhas externas.

## Dependencies

- Módulo `010` para eventos, rastreabilidade e observabilidade central
- Módulo `011` para estados comerciais, planos, assinaturas e faturas SaaS
- Módulo `012` para liquidações, falhas, conciliações e eventos financeiros
- Módulo `013` para casos de recuperação, promessas, escalonamentos e reengajamento
- Módulo `002` para autorização, auditoria e operação administrativa central
