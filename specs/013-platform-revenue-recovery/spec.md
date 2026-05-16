# Feature Specification: Módulo 013 - Platform Revenue Recovery

**Feature Branch**: `013-platform-revenue-recovery`  
**Created**: 2026-05-08  
**Status**: Draft  
**Input**: User description: "Prosseguir para o próximo módulo do roadmap após pagamentos e reconciliação, cobrindo recuperação de receita SaaS, dunning multicanal, escalonamento comercial e reengajamento após falhas."

## Contexto

Os módulos `011` e `012` fecharam o ciclo comercial e financeiro central do SaaS, mas ainda falta a camada de recuperação de receita após inadimplência, falhas de cobrança, divergências persistentes e chargebacks. O próximo passo é transformar o estado financeiro já observado em ação operacional coordenada.

Este módulo define a régua central de cobrança e reengajamento. Ele deve disparar lembretes, estruturar dunning por canais, escalar contas em atraso, preservar governança sobre promessas de pagamento e reduzir churn evitável sem misturar essa camada com o financeiro tenant.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Toda a régua de recuperação opera no plano central e nunca grava workflow comercial nos bancos tenant |
| Automated Financial Microservices | Reutiliza eventos e sinais dos módulos `010`, `011` e `012` para automatizar cobrança sem perder controle operacional |
| Operational Resilience & Disaster Recovery | Exige replay de campanhas, rastreabilidade de contatos, rollback seguro de estados comerciais e prevenção de mensagens duplicadas |
| Development Workflow & Quality Gates | Requer critérios auditáveis para lembretes, escalonamentos, promessas e bloqueios comerciais derivados de inadimplência |

## User Scenarios & Testing

### User Story 1 - Disparar régua de cobrança após falha ou atraso (Priority: P1)

Como operador da plataforma, quero que a régua de cobrança dispare automaticamente lembretes e próximos passos para faturas vencidas ou falhas de pagamento, para que a receita possa ser recuperada sem controle manual disperso.

**Why this priority**: Sem a primeira automação de dunning, o módulo `012` apenas registra falha e exceção, mas a plataforma continua dependendo de ação manual para cada inadimplência.

**Independent Test**: Pode ser validado colocando uma fatura SaaS em atraso ou com falha de cobrança e confirmando que a régua gera a próxima ação prevista com canal, prazo e motivo corretos.

**Acceptance Scenarios**:

1. **Given** uma fatura SaaS vencida sem liquidação confirmada, **When** a política de recuperação for avaliada, **Then** o sistema deve abrir a próxima ação de cobrança com canal, template e prazo compatíveis com a régua vigente.
2. **Given** uma cobrança que falhou no gateway, **When** a plataforma processar a falha, **Then** o sistema deve iniciar a régua de recuperação sem duplicar ações já abertas para a mesma obrigação.

---

### User Story 2 - Escalonar contas críticas e registrar compromissos (Priority: P2)

Como gestor comercial da plataforma, quero escalar contas reincidentes e registrar promessa de pagamento ou follow-up humano, para que inadimplência persistente não fique sem dono operacional.

**Why this priority**: Depois da automação inicial, a plataforma precisa separar casos comuns de casos críticos para evitar churn e permitir intervenção humana no momento correto.

**Independent Test**: Pode ser validado simulando uma conta com múltiplas falhas ou atraso acima do limite e confirmando criação de escalonamento, atribuição de responsável e registro de compromisso comercial.

**Acceptance Scenarios**:

1. **Given** um assinante com múltiplos ciclos de inadimplência ou exceções abertas sem resolução, **When** a política de escalonamento for aplicada, **Then** o sistema deve elevar o caso para acompanhamento humano com prioridade e dono definidos.
2. **Given** um contato comercial realizado com o assinante, **When** o operador registrar promessa de pagamento ou janela de retorno, **Then** o sistema deve preservar histórico, prazo acordado e impedir nova cobrança automática incompatível com esse compromisso antes do vencimento prometido.

---

### User Story 3 - Medir recuperação e reengajar assinantes em risco (Priority: P3)

Como super admin, quero enxergar métricas de recuperação, contas em risco e campanhas de reengajamento, para que a plataforma ajuste política de cobrança com base em resultado real.

**Why this priority**: O módulo só fecha o ciclo quando a operação consegue medir eficácia da régua e agir sobre churn iminente, não apenas reagir a exceções individuais.

**Independent Test**: Pode ser validado simulando contas em diferentes estágios da régua e confirmando que o painel central mostra taxa de recuperação, backlog de ações e casos prontos para reengajamento.

**Acceptance Scenarios**:

1. **Given** uma carteira com assinantes em estágios distintos de cobrança, **When** o super admin consultar o painel de recuperação, **Then** o sistema deve exibir indicadores por estágio, canal, atraso e conversão da régua.
2. **Given** um assinante que regularizou o débito após etapas de cobrança, **When** a recuperação for confirmada, **Then** o sistema deve encerrar a régua aberta, registrar a origem da recuperação e deixar o caso elegível para análise de retenção ou reengajamento futuro.

## Edge Cases

- Falha repetida do mesmo canal não pode disparar múltiplos lembretes idênticos para a mesma obrigação no mesmo estágio.
- Mudança de status da fatura entre a abertura e a execução da ação deve cancelar ou reavaliar a etapa pendente antes do envio.
- Promessa de pagamento registrada manualmente deve suspender apenas as ações incompatíveis, não toda a observabilidade do caso.
- Bloqueio comercial já aplicado pelo módulo `011` não pode ser revertido por uma automação de cobrança sem liquidação confirmada.
- Chargeback ou estorno após recuperação aparente deve reabrir a régua de forma rastreável sem apagar o histórico anterior.
- Em cenário de backup, restore ou rollback, o sistema deve reconstruir o último estágio consistente da régua sem reenviar contatos já confirmados como executados.

## Requirements

### Functional Requirements

- **FR-RR-001**: O sistema MUST permitir definir políticas centrais de recuperação de receita com estágios, canais, janelas de execução e critérios de escalonamento.
- **FR-RR-002**: O sistema MUST iniciar automaticamente uma régua de cobrança quando uma `FaturaSaaS` elegível entrar em atraso, falha de cobrança ou divergência financeira persistente.
- **FR-RR-003**: O sistema MUST impedir ações duplicadas de cobrança para a mesma obrigação, estágio e canal, exceto quando houver replay ou reabertura operacional autorizada.
- **FR-RR-004**: O sistema MUST registrar cada ação de recuperação com motivo, estágio, canal, prazo, resultado esperado e operador responsável quando houver intervenção humana.
- **FR-RR-005**: O sistema MUST suportar pelo menos lembrete automatizado, escalonamento humano e reengajamento pós-recuperação como tipos distintos de ação.
- **FR-RR-006**: O sistema MUST reavaliar a elegibilidade da ação imediatamente antes da execução para evitar contato indevido com fatura já liquidada, cancelada ou bloqueada por política incompatível.
- **FR-RR-007**: O sistema MUST permitir registrar promessa de pagamento, contato manual, tentativa sem sucesso e conclusão operacional do caso com trilha auditável.
- **FR-RR-008**: O sistema MUST escalar automaticamente casos reincidentes ou críticos conforme política central de severidade, atraso e histórico de falhas.
- **FR-RR-009**: O sistema MUST expor painel central com indicadores de recuperação, backlog operacional, estágios ativos, contas críticas e taxa de conversão por canal.
- **FR-RR-010**: O sistema MUST encerrar ou pausar a régua quando a obrigação financeira for regularizada, cancelada ou substituída por acordo válido.
- **FR-RR-011**: O sistema MUST publicar eventos centrais de recuperação de receita necessários ao backbone `010`, ao billing `011` e ao payments `012`.
- **FR-RR-012**: O sistema MUST preservar histórico completo das ações executadas, promessas, escalonamentos e resultados comerciais ligados a cada assinante e fatura.
- **FR-RR-013**: O sistema MUST permitir replay controlado de ações de cobrança falhas sem duplicar histórico já confirmado como entregue ou resolvido.
- **FR-RR-014**: Para esta feature com impacto direto em receita e comunicação, a spec MUST definir requisitos de backup, restore validation e rollback para estágios da régua, promessas e escalonamentos.

### Key Entities

- **PoliticaRecuperacaoReceita**: representa a política central que define estágios, critérios de entrada, janelas e escalonamentos da régua de cobrança.
- **CasoRecuperacaoReceita**: representa o acompanhamento operacional de um assinante ou fatura dentro da régua de recuperação.
- **AcaoRecuperacaoReceita**: representa um lembrete, contato, escalonamento, replay ou reengajamento planejado ou executado.
- **CompromissoPagamento**: representa a promessa ou acordo registrado manualmente com prazo e resultado esperado.
- **IndicadorRecuperacaoReceita**: representa agregações centrais para medir eficácia da régua, canais e resultados de recuperação.

## Success Criteria

### Measurable Outcomes

- **SC-RR-001**: 100% das faturas elegíveis para recuperação entram em um estágio rastreável da régua em até 5 minutos após a condição de atraso ou falha ser confirmada.
- **SC-RR-002**: Nenhuma obrigação financeira recebe mais de uma ação idêntica no mesmo estágio e canal sem autorização operacional explícita.
- **SC-RR-003**: Operadores conseguem identificar o próximo passo, o responsável e o último resultado de um caso crítico em até 3 interações.
- **SC-RR-004**: Pelo menos 90% dos casos regularizados têm a régua encerrada automaticamente sem intervenção manual adicional.
- **SC-RR-005**: O painel central consegue segmentar backlog, conversão e reincidência por canal e estágio sem depender de exportação manual.

## Dependencies

- Módulo `010` para eventos, replay, rastreabilidade e observabilidade central
- Módulo `011` para estados comerciais, grace period, bloqueio e saúde do assinante
- Módulo `012` para origem das falhas de cobrança, liquidações, exceções e chargebacks
- `MS-003` ou canal equivalente para notificações e automações de contato
- Módulo `002` para autorização, auditoria e operação administrativa central
