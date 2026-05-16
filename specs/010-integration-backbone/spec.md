# Feature Specification: Módulo 010 - Backbone de Integração e Observabilidade

**Feature Branch**: `010-integration-backbone`
**Created**: 2026-05-06
**Status**: Draft
**Input**: User description: "Mapear lacunas após os módulos 001-009 e abrir o próximo módulo da sequência com base no escopo ainda não coberto."

## Contexto

Os módulos `001-009` cobrem o fluxo funcional do ERP e a camada de orquestração local para fiscal, cobrança, garantias, logística e financeiro. A lacuna remanescente no monorepo está no backbone transversal que conecta o ERP aos microserviços de forma padronizada, auditável e observável.

Este módulo define a camada canônica de integração do ERP com broker, contratos de eventos, outbox/inbox, replay operacional, API Gateway para chamadas síncronas controladas e observabilidade ponta a ponta. Ele não substitui a lógica dos módulos `005-009`, mas cria a infraestrutura comum para que eles publiquem, consumam, rastreiem e recuperem integrações de forma confiável.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Eventos, entregas e rastros operacionais respeitam o tenant ativo sem uso de `filial_id` |
| Automated Financial Microservices | Formaliza contratos e transporte entre ERP e microserviços |
| Operational Resilience & Disaster Recovery | Inclui replay, dead-letter, trilha de entrega e rollback operacional |
| Development Workflow & Quality Gates | Exige testes de contrato, rastreabilidade e monitoramento verificável |

## User Scenarios & Testing

### User Story 1 - Publicação confiável de eventos (Priority: P1)

Como ERP Core, quero publicar eventos de negócio de forma confiável e rastreável para que os microserviços recebam mudanças críticas sem perda, duplicidade indevida ou acoplamento direto.

**Why this priority**: Sem uma espinha dorsal confiável, os módulos já implementados continuam dependentes de integrações ad hoc e sem garantia operacional uniforme.

**Independent Test**: Pode ser validado publicando eventos como `VALE_FATURADO` e `COBRANCA_CRIAR_BOLETO` a partir do ERP, confirmando persistência em outbox, despacho, retry e confirmação de entrega.

**Acceptance Scenarios**:

1. **Given** uma operação comercial concluída, **When** o ERP registra o evento no backbone, **Then** o evento deve ser persistido com tenant, versão, chave de idempotência e status de entrega.
2. **Given** uma indisponibilidade temporária do broker ou do consumidor, **When** o dispatcher tentar publicar o evento, **Then** o backbone deve reter a entrega, aplicar retry e manter rastreabilidade sem perder o evento.

---

### User Story 2 - Consumo idempotente e replay operacional (Priority: P2)

Como operador técnico, quero reprocessar eventos e inspecionar falhas de integração para recuperar cenários de contingência sem gerar efeitos colaterais duplicados.

**Why this priority**: Depois da publicação confiável, a principal necessidade operacional é recuperar integrações falhas com segurança e transparência.

**Independent Test**: Pode ser validado simulando consumo duplicado, falha permanente e replay manual de um evento em dead-letter.

**Acceptance Scenarios**:

1. **Given** um evento já consumido anteriormente, **When** ele for recebido novamente, **Then** o backbone deve reconhecê-lo como duplicado e impedir reprocessamento indevido.
2. **Given** um evento em dead-letter por falha externa, **When** um operador autorizado solicitar replay, **Then** o sistema deve reenfileirar a mensagem com histórico completo da tentativa original.

---

### User Story 3 - Visibilidade e contratos compartilhados (Priority: P3)

Como time de plataforma, quero contratos de eventos versionados e dashboards operacionais centralizados para evoluir ERP e microserviços sem deriva semântica e com diagnóstico rápido de incidentes.

**Why this priority**: Contratos e observabilidade sustentam a manutenção de longo prazo, mas dependem da camada mínima de publicação e consumo já definida.

**Independent Test**: Pode ser validado registrando um contrato versionado, expondo métricas e consultando dashboards e trilhas de uma integração ponta a ponta.

**Acceptance Scenarios**:

1. **Given** um novo tipo de evento compartilhado entre ERP e microserviço, **When** ele for registrado no catálogo de contratos, **Then** sua versão, payload esperado e consumidores autorizados devem ficar documentados e auditáveis.
2. **Given** uma cadeia de integração em produção, **When** ocorrer falha ou degradação, **Then** o painel operacional deve mostrar latência, retries, dead-letter e último status por evento e por tenant.

## Edge Cases

- Evento gravado no banco, mas não publicado no broker por falha após commit, deve permanecer recuperável pelo dispatcher.
- Evento publicado com schema antigo deve continuar rastreável, com versionamento explícito e política de compatibilidade.
- Replay manual de evento que já produziu efeito externo não pode causar duplicidade funcional sem validação de idempotência.
- Queda do broker não pode interromper a transação principal do ERP além do limite operacional definido.
- Se backup, restore ou rollback forem executados durante contingência, o sistema deve preservar consistência entre outbox, inbox e histórico de entregas.

## Requirements

### Functional Requirements

- **FR-INT-001**: O sistema MUST persistir eventos de domínio do ERP em uma outbox transacional por tenant antes do envio ao broker.
- **FR-INT-002**: O sistema MUST atribuir a cada evento um tipo canônico, versão de contrato, chave de idempotência, origem funcional e correlação operacional.
- **FR-INT-003**: O sistema MUST disponibilizar um dispatcher assíncrono para publicar eventos pendentes no broker com retry controlado e visibilidade de status.
- **FR-INT-004**: O sistema MUST registrar uma inbox de consumo para impedir reprocessamento duplicado de eventos recebidos do broker ou de webhooks equivalentes.
- **FR-INT-005**: O sistema MUST manter trilha de entrega contendo tentativas, latência, erro final, timestamps e destino da integração.
- **FR-INT-006**: O sistema MUST suportar dead-letter e replay manual ou automatizado de eventos falhos, com autorização e auditoria apropriadas.
- **FR-INT-007**: O sistema MUST manter um catálogo versionado de contratos de eventos e integrações síncronas aceitas pelo ERP.
- **FR-INT-008**: O sistema MUST prover um API Gateway ou camada equivalente para chamadas síncronas controladas, com autenticação, rate limiting e rastreio.
- **FR-INT-009**: O sistema MUST expor métricas e painéis operacionais para volume, sucesso, falha, retry, dead-letter e latência por evento, serviço e tenant.
- **FR-INT-010**: O sistema MUST permitir inspeção operacional por tenant, tipo de evento, correlação e intervalo de tempo sem acesso direto a logs brutos.
- **FR-INT-011**: O sistema MUST manter compatibilidade operacional com os módulos `005-009`, evitando duplicar a lógica fiscal, bancária, logística ou de garantias já existente.
- **FR-INT-012**: Para esta feature de infraestrutura, a spec MUST definir requisitos de backup, restore validation e rollback para outbox, inbox, contratos e estados de entrega.

### Key Entities

- **EventoOutbox**: representa um evento de domínio gerado pelo ERP e ainda não confirmado como entregue ao backbone externo.
- **EventoInbox**: representa um evento recebido e controlado para consumo idempotente no ERP.
- **EntregaIntegracao**: representa cada tentativa de publicação, consumo, retry, dead-letter ou replay de um evento.
- **ContratoEvento**: representa o schema lógico versionado de um tipo de evento e seus consumidores autorizados.
- **EndpointIntegracao**: representa um destino síncrono controlado pelo gateway com políticas de autenticação, limite e observabilidade.

## Success Criteria

### Measurable Outcomes

- **SC-INT-001**: 100% dos eventos publicados pelo ERP ficam rastreáveis por tenant, tipo, versão e correlação.
- **SC-INT-002**: Eventos com falha transitória são reprocessados automaticamente sem intervenção manual em pelo menos 95% dos casos simples.
- **SC-INT-003**: Replays manuais de eventos falhos podem ser executados em menos de 2 minutos por um operador autorizado.
- **SC-INT-004**: O painel operacional identifica eventos em dead-letter, latência anormal e degradação por serviço em menos de 30 segundos após a ocorrência.
- **SC-INT-005**: O backbone impede consumo duplicado observável para 100% dos cenários cobertos por idempotência contratada.

## Dependencies

- Módulo 001 para contexto de tenant e isolamento
- Módulo 002 para autorização operacional e trilha de auditoria
- Módulos 005 a 009 como principais produtores e consumidores de eventos do ERP
- Especificações dos microserviços `MS-001` a `MS-005` para alinhamento de contratos externos
