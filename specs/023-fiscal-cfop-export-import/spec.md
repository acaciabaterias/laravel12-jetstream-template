# Feature Specification: Módulo 023 - Fiscal CFOP Export/Import

**Feature Branch**: `023-fiscal-cfop-export-import`  
**Created**: 2026-05-18  
**Status**: Draft  
**Input**: User description: "Prossiga."

## Contexto

O ERP já consolidou orquestração fiscal e bancária, backbone, billing central, relatórios executivos, internacionalização e múltiplas moedas. O roadmap ainda prevê regras fiscais e CFOPs para exportação e importação, e este módulo fecha a próxima camada crítica para operações interestaduais e internacionais com governança central, catálogo auditável e rollback seguro de regras fiscais.

O objetivo do módulo `023` é introduzir uma camada central para catálogo de CFOPs, enquadramentos de exportação/importação, classificação fiscal por cenário e publicação governada de regras materiais. O escopo inicial cobre o plano central administrativo e a projeção operacional das regras fiscais que serão consumidas pela orquestração do módulo `009`, sem reescrever o fluxo transacional de emissão existente.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Regras fiscais centrais ficam no catálogo central e são projetadas para uso operacional sem quebrar isolamento tenant |
| Product Governance | CFOPs, NCMs e cenários fiscais passam a ter publicação, inspeção e rollback auditáveis |
| Operational Resilience & Disaster Recovery | Regras degradadas podem ser revertidas para a última publicação saudável com restore lógico controlado |
| ERP as Strategic Operating System | Operação fiscal deixa de depender de planilhas ou cadastros manuais sem governança para cenários de exportação/importação |

## User Scenarios & Testing

### User Story 1 - Consultar enquadramento fiscal por cenário de exportação/importação (Priority: P1)

Como operador fiscal da plataforma, quero consultar o enquadramento sugerido de CFOP e classificação por cenário fiscal, para validar rapidamente operações de exportação e importação sem depender de busca manual externa.

**Why this priority**: Sem consulta central reutilizável, o catálogo fiscal não entrega valor operacional imediato para conferência e suporte.

**Independent Test**: Pode ser validado autenticando um operador fiscal, selecionando um cenário de exportação ou importação e confirmando que o painel central retorna CFOP, direção fiscal, natureza da operação e flags de validação compatíveis com a regra publicada.

**Acceptance Scenarios**:

1. **Given** uma regra ativa compatível com exportação direta, **When** o operador consultar o cenário fiscal correspondente, **Then** o sistema deve retornar o CFOP sugerido, o enquadramento e os requisitos de validação material.
2. **Given** um cenário sem regra ativa ou com classificação inválida, **When** a consulta fiscal for executada, **Then** o sistema deve cair no fallback governado e sinalizar a lacuna sem quebrar a leitura operacional.

---

### User Story 2 - Publicar catálogo governado de CFOPs e regras fiscais (Priority: P2)

Como analista fiscal da plataforma, quero publicar versões governadas de CFOPs e regras de exportação/importação com validação mínima, para liberar cenários novos sem expor emissões a classificações incoerentes.

**Why this priority**: A consulta por cenário só é segura se houver governança explícita sobre quais regras fiscais estão realmente prontas para uso.

**Independent Test**: Pode ser validado publicando uma combinação controlada de CFOPs e regras fiscais, registrando o snapshot do catálogo, a cobertura mínima de cenários obrigatórios e as inconsistências detectadas.

**Acceptance Scenarios**:

1. **Given** um conjunto coerente de CFOPs e cenários obrigatórios cobertos, **When** o analista publicar a configuração ativa, **Then** o sistema deve registrar a publicação com snapshot fiscal e cobertura do recorte obrigatório.
2. **Given** um CFOP inválido, regra sem cenário ou combinação materialmente incoerente, **When** a publicação for executada, **Then** o sistema deve bloquear a promoção ou registrar a degradação sem perder a última publicação saudável.

---

### User Story 3 - Inspecionar regras fiscais e reverter publicação degradada (Priority: P3)

Como super admin, quero inspecionar histórico de publicações fiscais, inconsistências materiais e cenários descobertos, e executar rollback quando uma versão degradar o enquadramento, para manter a plataforma operável e auditável.

**Why this priority**: Regras fiscais sem inspeção e rollback criam risco direto para emissão, compliance e suporte operacional.

**Independent Test**: Pode ser validado simulando uma publicação com CFOP inconsistente ou cenário obrigatório ausente, verificando que a inspeção expõe a degradação e que o rollback restaura a última publicação saudável.

**Acceptance Scenarios**:

1. **Given** uma publicação ativa com regra fiscal inconsistente para um cenário obrigatório, **When** o super admin consultar a inspeção central, **Then** o sistema deve expor o cenário afetado, o CFOP publicado e a evidência da inconsistência.
2. **Given** uma publicação ativa considerada insegura, **When** o super admin executar rollback, **Then** o sistema deve restaurar a última publicação elegível e registrar a reversão com trilha auditável.

## Edge Cases

- Um cenário fiscal não pode apontar para CFOP inexistente no catálogo da publicação ativa.
- Uma publicação não pode remover cenários obrigatórios de exportação/importação sem registrar a lacuna material correspondente.
- Regras fiscais não podem misturar direção de operação incompatível com o cenário declarado.
- Rollback de publicação não pode apagar histórico do catálogo, das inconsistências e das decisões operacionais associadas.
- Em restore ou rollback de produção, a plataforma deve reconstruir a última publicação saudável e seus cenários obrigatórios sem reabrir regras despublicadas.

## Requirements

### Functional Requirements

- **FR-FISC-001**: O sistema MUST manter um catálogo central de CFOPs, classificações fiscais e cenários de exportação/importação versionáveis.
- **FR-FISC-002**: O sistema MUST permitir consultar enquadramento fiscal por cenário operacional, retornando CFOP sugerido, direção, natureza da operação e flags de validação.
- **FR-FISC-003**: O sistema MUST aplicar fallback governado quando um cenário fiscal não possuir regra ativa compatível.
- **FR-FISC-004**: O sistema MUST publicar combinações versionadas de CFOPs e regras fiscais com snapshot do catálogo e cobertura mínima dos cenários obrigatórios.
- **FR-FISC-005**: O sistema MUST validar coerência entre CFOP, direção fiscal, categoria da operação e cenário publicado antes da promoção da publicação ativa.
- **FR-FISC-006**: O sistema MUST registrar lacunas ou inconsistências materiais de regras fiscais como evidência inspecionável e rastreável.
- **FR-FISC-007**: O sistema MUST expor painel e inspeção reutilizável com publicação ativa, catálogo, cenários cobertos e inconsistências detectadas.
- **FR-FISC-008**: O sistema MUST permitir rollback para a última publicação saudável sem perder histórico de publicações e inconsistências.
- **FR-FISC-009**: O sistema MUST publicar eventos materiais de catálogo fiscal no backbone `010` para publicação e rollback.
- **FR-FISC-010**: O sistema MUST preservar o uso central como catálogo governado, sem duplicar estado fiscal definitivo em bancos tenant neste módulo.
- **FR-FISC-011**: A spec MUST definir requisitos de backup, restore validation e rollback para publicações fiscais e evidências de inconsistência.

### Key Entities

- **FiscalOperationScenario**: representa um cenário fiscal governado de exportação ou importação com direção, natureza da operação e critérios de enquadramento.
- **FiscalCfopCatalogEntry**: representa um CFOP publicado com descrição, direção fiscal e metadados operacionais.
- **FiscalRulePublicationRecord**: representa a publicação governada do catálogo fiscal e das regras ativas.
- **FiscalRuleMapping**: representa o vínculo entre cenário fiscal, CFOP, classificação e flags materiais da publicação.
- **FiscalRuleIssueReport**: representa uma inconsistência material ou lacuna detectada na publicação fiscal.

## Success Criteria

### Measurable Outcomes

- **SC-FISC-001**: Um operador fiscal autenticado consegue consultar o enquadramento de um cenário obrigatório em até uma interação no painel central.
- **SC-FISC-002**: Toda publicação ativa expõe catálogo de CFOPs, cenários cobertos e snapshot fiscal sem depender de planilhas externas.
- **SC-FISC-003**: Inconsistências materiais de regras fiscais ficam inspecionáveis em até 1 minuto após a publicação.
- **SC-FISC-004**: Um rollback validado restaura a última publicação saudável em até 3 interações no painel administrativo.
- **SC-FISC-005**: Nenhuma consulta fiscal central responde com cenário obrigatório sem classificação explícita, fallback ou evidência de lacuna.

## Dependencies

- Módulo `002` para autenticação e RBAC de `UsuarioPlataforma`
- Módulo `009` como consumidor fiscal primário do catálogo governado
- Módulo `010` para publicação de eventos materiais e trilha operacional
- Módulos `019` a `022` como consumidores indiretos de relatórios e leitura central de classificação fiscal
