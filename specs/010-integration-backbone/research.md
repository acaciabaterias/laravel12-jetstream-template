# Research: Módulo 010 - Backbone de Integração e Observabilidade

## Decision 1: Outbox/inbox transacional por tenant

**Decision**: Adotar outbox e inbox persistidas no banco do tenant como fonte de verdade operacional para publicação e consumo.

**Rationale**: O ERP já é tenant-aware e precisa garantir rastreabilidade completa sem depender apenas de Redis ou de logs voláteis. Persistir o estado no banco permite replay, reconciliação e inspeção por tenant.

**Alternatives considered**:
- Publicação direta no broker sem outbox: descartada por risco de perda entre commit e publish.
- Estado somente em Redis: descartado por baixa auditabilidade e recuperação mais fraca.

## Decision 2: Broker assíncrono separado do gateway síncrono

**Decision**: Tratar broker e API Gateway como responsabilidades diferentes dentro do backbone.

**Rationale**: O broker cobre eventos de domínio e desacoplamento assíncrono; o gateway cobre chamadas síncronas excepcionais com autenticação, rate limit e correlação. Misturar ambos no mesmo fluxo aumentaria ambiguidade operacional.

**Alternatives considered**:
- Usar apenas chamadas HTTP síncronas: descartado por acoplamento excessivo e menor resiliência.
- Usar apenas broker para tudo: descartado porque alguns fluxos exigem resposta síncrona controlada.

## Decision 3: Contratos versionados por evento

**Decision**: Versionar cada tipo de evento explicitamente e manter um catálogo de contratos no ERP.

**Rationale**: Os módulos `005-009` e os microserviços `MS-001` a `MS-005` já compartilham semânticas críticas. Sem catálogo versionado, há deriva de payload, incompatibilidade silenciosa e replay inseguro.

**Alternatives considered**:
- Versionamento implícito por naming convention: descartado por baixa governança.
- Documentação dispersa por microserviço: descartada por falta de visão central.

## Decision 4: Idempotência em dois níveis

**Decision**: Aplicar idempotência tanto no evento publicado quanto no efeito consumido.

**Rationale**: O replay e o retry seguros exigem distinguir “mesmo envelope” de “mesmo efeito de negócio”. Isso reduz duplicidade documental, bancária e logística.

**Alternatives considered**:
- Idempotência apenas no publisher: insuficiente para replay e reconsumo.
- Idempotência apenas no consumer: insuficiente para rastrear tentativas e entregas.

## Decision 5: Observabilidade operacional centrada em entrega

**Decision**: Medir volume, latência, retries, falhas e dead-letter por tipo de evento, serviço e tenant.

**Rationale**: Métricas genéricas de fila não bastam para operação de negócio. O objetivo é responder rapidamente “qual tenant”, “qual evento”, “qual serviço” e “qual estágio falhou”.

**Alternatives considered**:
- Observabilidade apenas por logs: descartada por baixa velocidade diagnóstica.
- Observabilidade apenas por serviço externo: descartada por ausência de visão ponta a ponta no ERP.

## Decision 6: Compatibilidade incremental com módulos existentes

**Decision**: O módulo `010` deve envolver os produtores e consumidores já existentes sem reescrever a lógica de negócio dos módulos `005-009`.

**Rationale**: O objetivo é consolidar a infraestrutura transversal, não reabrir os módulos funcionais já estabilizados.

**Alternatives considered**:
- Refatoração massiva imediata de todos os módulos: descartada por alto risco e baixo retorno de curto prazo.
