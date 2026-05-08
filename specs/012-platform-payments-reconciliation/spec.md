# Feature Specification: Módulo 012 - Platform Payments and Reconciliation

**Feature Branch**: `012-platform-payments-reconciliation`  
**Created**: 2026-05-08  
**Status**: Draft  
**Input**: User description: "Abrir o próximo módulo do roadmap após o control plane comercial, cobrindo pagamentos SaaS, webhooks, baixa automática e reconciliação."

## Contexto

Os módulos `010` e `011` fecharam o backbone operacional e o plano de controle comercial central. A lacuna seguinte está no ciclo financeiro externo da plataforma: cobrança efetiva do SaaS, processamento de retornos do gateway, conciliação de recebimentos, exceções operacionais e recuperação segura de falhas.

Este módulo define a camada de pagamentos do SaaS. Ele conecta faturas centrais a provedores de cobrança, trata webhooks e retornos assíncronos, aplica baixa automática quando elegível, identifica divergências de liquidação, mantém trilha auditável de exceções e dá visibilidade operacional ao time da plataforma.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Toda liquidação e reconciliação acontecem no plano central sem vazar dados financeiros entre tenants |
| Automated Financial Microservices | Formaliza integração controlada com gateways e retornos financeiros externos |
| Operational Resilience & Disaster Recovery | Exige idempotência de webhook, reconciliação repetível, backup e rollback para estados de cobrança críticos |
| Development Workflow & Quality Gates | Requer rastreabilidade de recebimentos, estornos, falhas de baixa e exceções de conciliação |

## User Scenarios & Testing

### User Story 1 - Cobrar faturas SaaS no gateway (Priority: P1)

Como operador da plataforma, quero registrar cobranças SaaS em um gateway externo para que faturas centrais possam ser pagas por boleto, PIX ou outro meio suportado sem controle manual paralelo.

**Why this priority**: Sem emissão e sincronização com o provedor de pagamento, o módulo `011` continua com cobrança apenas conceitual e não fecha o ciclo financeiro do SaaS.

**Independent Test**: Pode ser validado emitindo uma fatura SaaS elegível, enviando-a ao gateway configurado e confirmando que a plataforma recebe identificador externo, meio de pagamento, vencimento e estado inicial da cobrança.

**Acceptance Scenarios**:

1. **Given** uma fatura SaaS aberta e elegível para cobrança, **When** a plataforma solicitar a emissão no gateway, **Then** o sistema deve registrar o identificador externo, o meio de pagamento, a referência comercial e o estado operacional da cobrança.
2. **Given** uma fatura já enviada ao gateway, **When** o operador tentar reenviar a mesma solicitação sem necessidade operacional válida, **Then** o sistema deve evitar duplicidade e preservar a rastreabilidade da tentativa.

---

### User Story 2 - Conciliar retornos e baixar faturas automaticamente (Priority: P2)

Como gestor financeiro da plataforma, quero processar webhooks e retornos do provedor para que recebimentos confirmados atualizem a fatura SaaS correta sem intervenção manual em fluxos normais.

**Why this priority**: Depois da emissão, o maior risco operacional é receber confirmação externa e não refletir isso corretamente no estado financeiro central.

**Independent Test**: Pode ser validado simulando webhook ou retorno assíncrono de pagamento, conciliando com a cobrança certa e verificando baixa automática, prevenção de duplicidade e trilha de auditoria.

**Acceptance Scenarios**:

1. **Given** uma cobrança externa quitada, **When** o retorno do provedor chegar com referência válida, **Then** o sistema deve localizar a fatura correspondente, registrar a liquidação e atualizar o estado comercial associado.
2. **Given** um webhook repetido ou fora de ordem, **When** a plataforma processar o evento, **Then** o sistema deve tratá-lo de forma idempotente sem duplicar baixa, evento ou auditoria financeira.

---

### User Story 3 - Operar divergências e exceções de reconciliação (Priority: P3)

Como super admin financeiro, quero visualizar divergências, falhas de baixa e exceções de pagamento para corrigir rapidamente cobranças inconsistentes e reduzir perda de receita.

**Why this priority**: A camada operacional só gera valor total quando o time consegue agir sobre pagamentos não conciliados, estornos, chargebacks e falhas de integração.

**Independent Test**: Pode ser validado criando retornos com valor divergente, cobrança expirada, estorno ou chargeback e confirmando que a plataforma classifica a exceção e disponibiliza ação operacional rastreável.

**Acceptance Scenarios**:

1. **Given** um retorno com valor, referência ou status divergente da fatura central, **When** a conciliação automática não puder fechar o caso, **Then** o sistema deve classificar a ocorrência como exceção operacional e manter o caso pendente para análise.
2. **Given** uma exceção de pagamento já identificada, **When** o super admin consultar a visão operacional, **Then** o sistema deve mostrar causa, impacto comercial, histórico de tentativas e próximo passo recomendado.

## Edge Cases

- Webhook duplicado, atrasado ou entregue fora de ordem não pode gerar baixa múltipla nem reabrir fatura já liquidada.
- Estorno parcial ou chargeback posterior à liquidação deve preservar o histórico do recebimento original e abrir divergência explícita.
- Falha temporária de comunicação com o gateway não pode deixar a fatura em estado ambíguo entre "não enviada" e "emitida".
- Referência externa inexistente ou inválida deve ser segregada como exceção operacional sem contaminar outras faturas.
- Alteração de valor ou vencimento após emissão no gateway deve seguir política clara de atualização, cancelamento ou reemissão.
- Se backup, restore ou rollback forem executados durante emissão, baixa ou reconciliação, o sistema deve restaurar o último estado financeiro consistente sem duplicar eventos de cobrança.

## Requirements

### Functional Requirements

- **FR-PAY-001**: O sistema MUST permitir configurar pelo menos um provedor de cobrança SaaS com credenciais, meios de pagamento suportados e estado operacional controlado.
- **FR-PAY-002**: O sistema MUST permitir emitir cobranças externas para faturas SaaS elegíveis, preservando vínculo inequívoco entre fatura central e identificador externo do provedor.
- **FR-PAY-003**: O sistema MUST impedir criação duplicada de cobrança para a mesma obrigação financeira, exceto quando houver reemissão operacional autorizada e rastreável.
- **FR-PAY-004**: O sistema MUST registrar status operacional da cobrança externa, incluindo criação, expiração, liquidação, cancelamento, falha, estorno e chargeback quando aplicável.
- **FR-PAY-005**: O sistema MUST aceitar retornos assíncronos do provedor e processá-los de forma idempotente com validação mínima de origem, referência e integridade operacional.
- **FR-PAY-006**: O sistema MUST conciliar automaticamente recebimentos confirmados quando a referência, o valor e o estado esperado permitirem baixa segura da fatura central.
- **FR-PAY-007**: O sistema MUST encaminhar para fila de exceções operacionais qualquer retorno que não possa ser conciliado automaticamente sem risco de baixa incorreta.
- **FR-PAY-008**: O sistema MUST manter trilha auditável de emissão, reemissão, baixa, falha, cancelamento, estorno, chargeback e intervenção manual associada à cobrança SaaS.
- **FR-PAY-009**: O sistema MUST atualizar o estado comercial do assinante quando a liquidação ou a divergência financeira afetar grace period, bloqueio, reativação ou cancelamento.
- **FR-PAY-010**: O sistema MUST disponibilizar visão operacional central para cobranças emitidas, pendentes, liquidadas, divergentes, estornadas e não conciliadas.
- **FR-PAY-011**: O sistema MUST permitir replay controlado ou reprocessamento operacional de retornos financeiros quando houver falha transitória ou recuperação pós-incidente.
- **FR-PAY-012**: O sistema MUST publicar eventos financeiros centrais necessários ao backbone `010` para notificação, auditoria e integração operacional correlata.
- **FR-PAY-013**: O sistema MUST definir política explícita para reemissão, atualização ou cancelamento de cobrança quando valor ou vencimento forem alterados após integração externa.
- **FR-PAY-014**: Para esta feature com impacto direto em receita, a spec MUST definir requisitos de backup, restore validation e rollback para estados de cobrança, liquidação e reconciliação.

### Key Entities

- **GatewayCobrancaSaaS**: representa o provedor externo habilitado para emitir e acompanhar cobranças da plataforma.
- **CobrancaSaaSExterna**: representa a materialização externa de uma fatura SaaS em um gateway de pagamento.
- **RetornoPagamentoSaaS**: representa webhook, callback ou arquivo de retorno associado a uma cobrança emitida.
- **ConciliacaoPagamentoSaaS**: representa o resultado da comparação entre retorno externo e obrigação financeira central.
- **ExcecaoConciliacaoSaaS**: representa casos que exigem análise humana, como divergência de valor, referência inválida, chargeback ou estorno.

## Success Criteria

### Measurable Outcomes

- **SC-PAY-001**: 100% das faturas SaaS elegíveis podem ser enviadas ao provedor configurado com vínculo rastreável entre referência interna e identificador externo.
- **SC-PAY-002**: Pelo menos 95% dos recebimentos válidos são conciliados automaticamente sem intervenção manual.
- **SC-PAY-003**: Webhooks ou retornos duplicados não geram mais de um efeito financeiro para a mesma cobrança.
- **SC-PAY-004**: Exceções de conciliação ficam visíveis ao operador em até 1 minuto após o retorno não conciliado ser recebido.
- **SC-PAY-005**: Operadores autorizados conseguem identificar causa e próximo passo de uma divergência de pagamento em até 3 interações.

## Dependencies

- Módulo `010` para publicação, replay e rastreabilidade dos eventos centrais de pagamento
- Módulo `011` para vínculo entre faturas SaaS, estados comerciais do assinante e bloqueio/reativação
- `MS-002` ou gateway equivalente para fluxos bancários, boleto, PIX e retornos financeiros externos
- Módulo `002` para autorização, auditoria e operação administrativa central
