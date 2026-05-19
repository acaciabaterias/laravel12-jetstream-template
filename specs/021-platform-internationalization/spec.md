# Feature Specification: Módulo 021 - Platform Internationalization

**Feature Branch**: `021-platform-internationalization`  
**Created**: 2026-05-18  
**Status**: Draft  
**Input**: User description: "Pode abrir o 021 agora, seguindo o mesmo padrão dos módulos anteriores: spec, plan, research, data-model, quickstart, contratos, tasks e implementação."

## Contexto

O ERP já utiliza `__()` em boa parte das views de autenticação, perfil e navegação, mas ainda opera com locale único implícito e sem governança central para publicação, fallback, cobertura mínima e rollback do catálogo de idiomas. O roadmap prevê suporte a `pt-BR`, `en` e `es`, e este módulo fecha a primeira camada operacional dessa expansão.

O objetivo do módulo `021` é internacionalizar o plano central da plataforma sem quebrar os módulos já entregues. Ele deve resolver locale por request, persistir preferência por operador, publicar conjuntos de idiomas suportados com fallback explícito, medir cobertura mínima de chaves centrais e permitir inspeção e rollback auditável quando uma publicação de idioma degradar a experiência.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | A resolução de locale ocorre no plano central e não desloca estado de idioma para bancos tenant |
| Product Governance | Idiomas suportados, fallback e cobertura mínima passam a ter publicação e rollback auditáveis |
| Operational Resilience & Disaster Recovery | O módulo exige restore lógico da publicação ativa e rollback governado da última combinação saudável |
| ERP as Strategic Operating System | Internacionalização deixa de depender de edição manual de arquivos sem trilha de governança |

## User Scenarios & Testing

### User Story 1 - Alternar idioma da plataforma por operador (Priority: P1)

Como operador do plano central, quero escolher meu idioma preferido e ver a interface administrativa refletir essa escolha no request seguinte, para operar o ERP no idioma mais adequado sem depender de alteração global da aplicação.

**Why this priority**: Sem resolução por operador, o suporte multilíngue não passa de arquivo estático e não entrega valor operacional real.

**Independent Test**: Pode ser validado autenticando um usuário de plataforma, alterando o locale preferido para `en` ou `es` e confirmando que o painel administrativo e a autenticação passam a responder com textos compatíveis com o idioma selecionado.

**Acceptance Scenarios**:

1. **Given** um operador autenticado com preferência de idioma suportada, **When** ele acessar o painel central, **Then** a aplicação deve responder no locale persistido para aquele operador.
2. **Given** uma preferência de idioma inválida ou não publicada, **When** o request for resolvido, **Then** o sistema deve usar o fallback ativo sem quebrar a renderização.

---

### User Story 2 - Publicar idiomas suportados com cobertura mínima e fallback (Priority: P2)

Como analista de suporte da plataforma, quero publicar o conjunto de idiomas suportados e o fallback ativo com validação de cobertura mínima, para liberar novos idiomas sem expor telas críticas com lacunas silenciosas.

**Why this priority**: A troca por operador só é segura se houver governança sobre quais idiomas estão realmente prontos para uso.

**Independent Test**: Pode ser validado publicando uma combinação controlada de idiomas suportados, verificando que a publicação registra o fallback ativo, gera snapshot de cobertura e marca chaves ausentes como itens de atenção.

**Acceptance Scenarios**:

1. **Given** um conjunto de idiomas com cobertura suficiente para o recorte central, **When** o suporte publicar a configuração ativa, **Then** o sistema deve registrar a publicação com fallback e snapshot de cobertura por locale.
2. **Given** um idioma com chaves centrais ausentes, **When** a publicação for executada, **Then** o sistema deve registrar a lacuna como evidência inspecionável sem perder a última publicação saudável.

---

### User Story 3 - Inspecionar cobertura e reverter publicações degradadas (Priority: P3)

Como super admin, quero inspecionar cobertura, lacunas e histórico de publicações de idioma e executar rollback quando uma publicação degradar a experiência, para manter o plano central utilizável em todos os idiomas liberados.

**Why this priority**: Internacionalização sem inspeção e rollback cria risco operacional direto em login, navegação e painéis centrais.

**Independent Test**: Pode ser validado simulando uma publicação com cobertura inferior ou fallback inconsistente, verificando que a inspeção expõe a degradação e que o rollback restaura a última publicação saudável.

**Acceptance Scenarios**:

1. **Given** uma publicação ativa com lacunas críticas de tradução, **When** o super admin consultar a inspeção central, **Then** o sistema deve expor o locale afetado, a cobertura medida e as chaves ausentes.
2. **Given** uma publicação ativa considerada insegura, **When** o super admin executar rollback, **Then** o sistema deve restaurar a publicação anterior elegível e registrar evidência auditável da reversão.

## Edge Cases

- Um operador não pode persistir locale fora da lista suportada ou já removido da publicação ativa.
- Um request autenticado não pode alternar para locale indisponível e renderizar metade da interface em idioma diferente do fallback.
- Uma publicação de idioma não pode remover o fallback ativo sem eleger outro fallback válido na mesma operação.
- Lacunas de tradução em chaves centrais devem ser detectadas sem quebrar a leitura do painel ou da tela de login.
- Rollback de publicação não pode apagar o histórico das lacunas detectadas nem a associação entre publicação e operador responsável.
- Em restore ou rollback de produção, a plataforma deve reconstruir a última publicação saudável, o fallback ativo e as preferências de operador sem reabrir idiomas despublicados.

## Requirements

### Functional Requirements

- **FR-INT-001**: O sistema MUST suportar os locales `pt_BR`, `en` e `es` para o plano central da plataforma.
- **FR-INT-002**: O sistema MUST resolver o locale de cada request administrativo usando a preferência persistida do operador autenticado, desde que o locale esteja suportado pela publicação ativa.
- **FR-INT-003**: O sistema MUST aplicar fallback explícito quando a preferência do operador estiver ausente, inválida ou indisponível na publicação ativa.
- **FR-INT-004**: O sistema MUST permitir que operadores autorizados alterem e persistam sua preferência de idioma sem modificar o locale global da aplicação.
- **FR-INT-005**: O sistema MUST publicar combinações versionadas de idiomas suportados com locale padrão, fallback e snapshot de cobertura por idioma.
- **FR-INT-006**: O sistema MUST medir cobertura mínima do recorte central usando uma lista governada de chaves obrigatórias para autenticação, navegação e painel administrativo.
- **FR-INT-007**: O sistema MUST registrar chaves ausentes ou lacunas materiais por locale como evidência inspecionável e rastreável.
- **FR-INT-008**: O sistema MUST expor painel e inspeção reutilizável com publicação ativa, histórico, cobertura por locale, fallback e lacunas detectadas.
- **FR-INT-009**: O sistema MUST permitir rollback para a última publicação saudável sem perder histórico de preferências, publicações e lacunas.
- **FR-INT-010**: O sistema MUST publicar eventos materiais de internacionalização no backbone `010` para publicação e rollback de locale bundle.
- **FR-INT-011**: O sistema MUST preservar isolamento central, sem gravar estado de internacionalização nos bancos tenant.
- **FR-INT-012**: Para esta feature com impacto em configuração e experiência operacional, a spec MUST definir requisitos de backup, restore validation e rollback para preferências, publicações e evidências de lacuna.

### Key Entities

- **PlatformLocalePreference**: representa a preferência persistida de idioma por operador da plataforma.
- **PlatformLocalePublicationRecord**: representa uma publicação governada de idiomas suportados, locale padrão, fallback e snapshot de cobertura.
- **PlatformLocaleMissingKeyReport**: representa uma lacuna material de tradução detectada para um locale e uma chave obrigatória.
- **PlatformLocaleCoverageSnapshot**: representa a visão agregada de cobertura medida por locale no momento da publicação ou inspeção.

## Success Criteria

### Measurable Outcomes

- **SC-INT-001**: Um operador autenticado consegue alterar idioma e ver o painel central renderizado no locale escolhido em até um novo request.
- **SC-INT-002**: Toda publicação ativa expõe cobertura por locale e fallback válido sem depender de checagem manual em arquivos.
- **SC-INT-003**: Lacunas de tradução em chaves obrigatórias do recorte central ficam inspecionáveis em até 1 minuto após a publicação.
- **SC-INT-004**: Um rollback validado restaura a última publicação saudável em até 3 interações no painel administrativo.
- **SC-INT-005**: Nenhum request autenticado do plano central responde com locale fora da lista suportada pela publicação ativa.

## Dependencies

- Módulo `002` para autenticação e RBAC de `UsuarioPlataforma`
- Módulo `010` para publicação de eventos materiais e trilha operacional
- Módulo `011` a `020` como consumidores indiretos da resolução central de locale no painel administrativo
- Laravel localization (`lang`, `setLocale`, JSON translations, fallback) como base técnica da camada de idioma
