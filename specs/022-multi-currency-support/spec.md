# Feature Specification: Módulo 022 - Multi-Currency Support

**Feature Branch**: `022-multi-currency-support`  
**Created**: 2026-05-18  
**Status**: Draft  
**Input**: User description: "Prossiga e depois vamos iniciar o próximo módulo."

## Contexto

O ERP já consolidou billing, payments, recovery, analytics, relatórios executivos e internacionalização do plano central, mas ainda opera com moeda única implícita. O roadmap prevê suporte a múltiplas moedas, e este módulo fecha a camada inicial de governança monetária para que catálogos, assinaturas, cobranças SaaS e relatórios centrais possam operar com moeda de exibição e moeda base sem conversões manuais fora do sistema.

O objetivo do módulo `022` é introduzir suporte central a múltiplas moedas com catálogo governado, conjuntos versionados de taxas de câmbio, preferência monetária por operador e rollback auditável de publicações degradadas. O escopo inicial cobre o plano central e suas leituras operacionais, preservando `BRL` como moeda base contábil e habilitando exibição e comparação segura em moedas adicionais.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | O estado de moedas e câmbio fica no plano central sem deslocar consistência monetária para bancos tenant |
| Product Governance | Currencies suportadas e taxas ativas passam a ter publicação, inspeção e rollback auditáveis |
| Operational Resilience & Disaster Recovery | Publicações degradadas de câmbio exigem restore lógico e reversão segura da última tabela saudável |
| ERP as Strategic Operating System | Conversões deixam de depender de planilhas ou ajustes manuais fora do ERP |

## User Scenarios & Testing

### User Story 1 - Exibir valores centrais na moeda preferida do operador (Priority: P1)

Como operador do plano central, quero escolher minha moeda de exibição e ver valores monetários centrais convertidos de forma consistente a partir da moeda base, para analisar billing, recovery e analytics sem fazer conversões manuais.

**Why this priority**: Sem preferência monetária por operador, o suporte a múltiplas moedas não entrega valor operacional real para leitura central.

**Independent Test**: Pode ser validado autenticando um operador da plataforma, definindo sua moeda preferida como `USD` ou `EUR` e confirmando que cards e totais administrativos passam a responder com moeda convertida e arredondamento consistente a partir do valor base em `BRL`.

**Acceptance Scenarios**:

1. **Given** um operador autenticado com moeda preferida suportada, **When** ele acessar um painel central com valores monetários, **Then** o sistema deve exibir o montante na moeda escolhida usando a tabela ativa de câmbio.
2. **Given** uma moeda preferida ausente, inválida ou não publicada, **When** um valor monetário central for renderizado, **Then** o sistema deve usar a moeda padrão ativa sem quebrar a leitura do valor base.

---

### User Story 2 - Publicar moedas suportadas e tabela de câmbio ativa (Priority: P2)

Como analista financeiro da plataforma, quero publicar o conjunto de moedas suportadas e a tabela ativa de taxas com validação mínima, para liberar novas moedas sem expor billing ou relatórios a conversões incoerentes.

**Why this priority**: A preferência por operador só é segura se houver governança sobre quais moedas e taxas estão prontas para uso.

**Independent Test**: Pode ser validado publicando um conjunto controlado com `BRL`, `USD` e `EUR`, registrando a moeda base, a tabela de taxas e a cobertura de conversões obrigatórias para o recorte central.

**Acceptance Scenarios**:

1. **Given** um conjunto de moedas com taxa válida em relação à moeda base, **When** o financeiro publicar a configuração ativa, **Then** o sistema deve registrar a publicação com moeda base, snapshot de taxas e metadata de cobertura mínima.
2. **Given** uma moeda sem taxa válida, taxa não positiva ou tabela inconsistente, **When** a publicação for executada, **Then** o sistema deve bloquear a promoção ou registrar a degradação sem perder a última publicação saudável.

---

### User Story 3 - Inspecionar conversões e reverter tabela degradada (Priority: P3)

Como super admin, quero inspecionar histórico de moedas e taxas publicadas e executar rollback quando uma tabela degradar a leitura monetária, para manter o plano central operável e comparável em todas as moedas liberadas.

**Why this priority**: Múltiplas moedas sem inspeção e rollback criam risco direto para billing, analytics e exportações executivas.

**Independent Test**: Pode ser validado simulando uma publicação com taxa inconsistente ou moeda ausente, verificando que a inspeção expõe a degradação e que o rollback restaura a última publicação saudável.

**Acceptance Scenarios**:

1. **Given** uma publicação ativa com taxa inconsistente para uma moeda suportada, **When** o super admin consultar a inspeção central, **Then** o sistema deve expor a moeda afetada, a taxa ativa e a evidência da inconsistência.
2. **Given** uma tabela ativa considerada insegura, **When** o super admin executar rollback, **Then** o sistema deve restaurar a última publicação elegível e registrar a reversão com trilha auditável.

## Edge Cases

- Um operador não pode persistir moeda preferida fora da lista suportada ou removida da publicação ativa.
- Uma publicação não pode remover a moeda base sem eleger outra base válida e recomputar a cobertura mínima das conversões obrigatórias.
- Taxas de câmbio não podem ser zero, negativas ou ausentes para moedas marcadas como suportadas.
- Conversão de valores históricos deve preservar o montante base original em `BRL` e identificar a taxa usada na renderização.
- Rollback de tabela de câmbio não pode apagar histórico das publicações, das taxas e das preferências de operador.
- Em restore ou rollback de produção, a plataforma deve reconstruir a última publicação saudável e a moeda padrão sem reabrir moedas despublicadas.

## Requirements

### Functional Requirements

- **FR-MCS-001**: O sistema MUST suportar `BRL` como moeda base inicial da plataforma e permitir moedas adicionais publicadas de forma governada.
- **FR-MCS-002**: O sistema MUST permitir publicar moedas suportadas com código ISO, símbolo, escala decimal e status operacional.
- **FR-MCS-003**: O sistema MUST resolver a moeda de exibição de cada request administrativo usando a preferência persistida do operador, desde que essa moeda esteja suportada pela publicação ativa.
- **FR-MCS-004**: O sistema MUST aplicar moeda padrão ativa quando a preferência do operador estiver ausente, inválida ou indisponível na publicação ativa.
- **FR-MCS-005**: O sistema MUST publicar combinações versionadas de moedas suportadas com moeda base, moeda padrão e snapshot das taxas de câmbio.
- **FR-MCS-006**: O sistema MUST validar que cada moeda suportada possui taxa positiva e consistente em relação à moeda base antes da promoção da publicação ativa.
- **FR-MCS-007**: O sistema MUST registrar lacunas ou inconsistências materiais de conversão como evidência inspecionável e rastreável.
- **FR-MCS-008**: O sistema MUST expor painel e inspeção reutilizável com publicação ativa, histórico, moedas suportadas, taxas e inconsistências detectadas.
- **FR-MCS-009**: O sistema MUST permitir rollback para a última publicação saudável sem perder histórico de preferências, publicações e evidências de inconsistência.
- **FR-MCS-010**: O sistema MUST publicar eventos materiais de múltiplas moedas no backbone `010` para publicação e rollback da tabela ativa.
- **FR-MCS-011**: O sistema MUST preservar o valor base original em `BRL` para leitura central, usando conversão apenas como projeção operacional de exibição.
- **FR-MCS-012**: A spec MUST definir requisitos de backup, restore validation e rollback para preferências monetárias, publicações e evidências de inconsistência.

### Key Entities

- **PlatformCurrencyPreference**: representa a moeda de exibição persistida por operador da plataforma.
- **PlatformCurrencyCatalogEntry**: representa uma moeda suportada com código ISO, símbolo, casas decimais e metadados operacionais.
- **PlatformExchangeRatePublicationRecord**: representa uma publicação governada da tabela ativa de moedas e taxas.
- **PlatformExchangeRateEntry**: representa a taxa de uma moeda suportada em relação à moeda base publicada.
- **PlatformConversionIssueReport**: representa uma inconsistência material de taxa, cobertura ou configuração detectada na publicação.

## Success Criteria

### Measurable Outcomes

- **SC-MCS-001**: Um operador autenticado consegue alterar sua moeda preferida e ver valores centrais renderizados na nova moeda em até um novo request.
- **SC-MCS-002**: Toda publicação ativa expõe moeda base, moeda padrão, moedas suportadas e snapshot de taxas sem depender de planilhas externas.
- **SC-MCS-003**: Inconsistências materiais de câmbio ficam inspecionáveis em até 1 minuto após a publicação.
- **SC-MCS-004**: Um rollback validado restaura a última tabela saudável em até 3 interações no painel administrativo.
- **SC-MCS-005**: Nenhum request central responde com moeda fora da lista suportada pela publicação ativa.

## Dependencies

- Módulo `002` para autenticação e RBAC de `UsuarioPlataforma`
- Módulo `010` para publicação de eventos materiais e trilha operacional
- Módulos `011` a `021` como consumidores da projeção monetária central
- Armazenamento monetário existente em `BRL` como valor base do plano central
