# Feature Specification: Módulo 011 - Platform Billing Control Plane

**Feature Branch**: `011-platform-billing-control-plane`
**Created**: 2026-05-07
**Status**: Draft
**Input**: User description: "Definir o próximo módulo do roadmap após o backbone 010, formalizando a camada SaaS central de planos, assinaturas, cobrança, bloqueio e visão operacional do assinante."

## Contexto

Os módulos `001-010` cobrem isolamento multi-tenant, operações ERP por assinante e backbone de integração. A lacuna remanescente está na camada central do SaaS: governança comercial do assinante, políticas de cobrança, ciclo de vida de planos e visibilidade operacional para o time da plataforma.

Este módulo define o plano de controle comercial do produto. Ele concentra cadastro de planos, gestão de assinaturas, emissão e acompanhamento de faturas SaaS, regras de bloqueio e desbloqueio, períodos de tolerância, trilha operacional e indicadores de saúde da base de assinantes.

## Constitution Mapping

| Principle | How this module satisfies |
|-----------|--------------------------|
| Multi-Tenancy Isolado (v2.0.0) | Toda decisão comercial é tomada no contexto central do assinante sem romper o isolamento físico dos tenants |
| Automated Financial Microservices | Define os gatilhos comerciais que alimentam cobrança, notificação e integrações financeiras |
| Operational Resilience & Disaster Recovery | Exige evidência de backup, restore validado e rollback para alterações de estado comercial críticas |
| Development Workflow & Quality Gates | Requer rastreabilidade de mudanças de plano, suspensão, desbloqueio e falhas de cobrança |

## User Scenarios & Testing

### User Story 1 - Gerir assinatura e plano do assinante (Priority: P1)

Como operador da plataforma, quero criar, ativar, alterar e encerrar assinaturas de forma controlada para que cada tenant tenha um plano comercial claro e auditável.

**Why this priority**: Sem uma assinatura central formalizada, bloqueio por inadimplência, cobrança recorrente e expansão comercial permanecem dependentes de regras dispersas.

**Independent Test**: Pode ser validado criando um plano, vinculando-o a um assinante, alterando o ciclo comercial e confirmando que histórico, vigência e estado final da assinatura ficam rastreáveis.

**Acceptance Scenarios**:

1. **Given** um novo assinante aprovado comercialmente, **When** a plataforma ativar uma assinatura, **Then** o sistema deve registrar plano, vigência, ciclo de cobrança e estado inicial do contrato.
2. **Given** uma assinatura ativa, **When** a plataforma trocar o plano ou encerrar o contrato, **Then** o sistema deve preservar histórico da mudança, motivo operacional e data efetiva.

---

### User Story 2 - Cobrar e aplicar política de inadimplência (Priority: P2)

Como gestor financeiro da plataforma, quero acompanhar faturas SaaS e aplicar tolerância, suspensão e reativação de forma padronizada para reduzir inconsistência operacional e risco comercial.

**Why this priority**: Após formalizar a assinatura, o próximo passo de valor é garantir cobrança previsível e política uniforme de inadimplência.

**Independent Test**: Pode ser validado gerando uma fatura SaaS, simulando atraso, aplicando grace period, bloqueio e posterior regularização com reativação.

**Acceptance Scenarios**:

1. **Given** uma cobrança vencida além da tolerância permitida, **When** a política de inadimplência for aplicada, **Then** o sistema deve marcar o assinante como bloqueável e registrar a ação operacional correspondente.
2. **Given** um assinante bloqueado por atraso, **When** a regularização financeira for confirmada, **Then** o sistema deve remover o bloqueio, registrar a reativação e restaurar a elegibilidade operacional do tenant.

---

### User Story 3 - Operar a saúde comercial da base (Priority: P3)

Como super admin, quero enxergar a saúde comercial da base de assinantes para priorizar cobrança, retenção, expansão e intervenções operacionais.

**Why this priority**: A camada analítica amplia a eficiência comercial, mas depende da base contratual e de cobrança já estarem confiáveis.

**Independent Test**: Pode ser validado consultando um painel com situação por assinante, carteira vencida, reativações recentes e mudanças de plano em um período definido.

**Acceptance Scenarios**:

1. **Given** a base de assinantes ativa, **When** o super admin consultar o painel central, **Then** o sistema deve mostrar status comercial, risco de bloqueio e eventos recentes por assinante.
2. **Given** uma carteira com atrasos e mudanças de plano, **When** o super admin filtrar por período ou status, **Then** o sistema deve retornar visão consolidada para ação operacional imediata.

## Edge Cases

- Alteração de plano com cobrança já emitida, mas ainda não liquidada, deve manter regra clara de vigência e responsabilidade financeira.
- Assinante reativado após bloqueio não pode perder histórico da suspensão nem da quitação que motivou o desbloqueio.
- Falha parcial na geração de cobrança não pode colocar a assinatura em estado ambíguo entre ativa, vencida e bloqueada.
- Encerramento voluntário com valores em aberto deve manter rastreabilidade do saldo remanescente e da data efetiva de término.
- Se backup, restore ou rollback forem executados durante mudança de plano, bloqueio ou reativação, o sistema deve restaurar o último estado comercial consistente sem duplicar eventos operacionais.

## Requirements

### Functional Requirements

- **FR-BILL-001**: O sistema MUST manter catálogo central de planos, ciclos comerciais e regras de elegibilidade por assinante.
- **FR-BILL-002**: O sistema MUST permitir criar, ativar, alterar, suspender, reativar e encerrar assinaturas com histórico completo de vigência e motivo operacional.
- **FR-BILL-003**: O sistema MUST vincular cada assinatura a um assinante central com identificação comercial e estado contratual inequívoco.
- **FR-BILL-004**: O sistema MUST gerar e acompanhar cobranças SaaS associadas à assinatura, incluindo vencimento, status, valor e referência do período cobrado.
- **FR-BILL-005**: O sistema MUST aplicar política configurável de tolerância, inadimplência, bloqueio e desbloqueio com base no estado das cobranças.
- **FR-BILL-006**: O sistema MUST distinguir bloqueio preventivo, bloqueio efetivo, reativação e cancelamento definitivo em trilha auditável.
- **FR-BILL-007**: O sistema MUST disponibilizar painel central para o super admin com situação comercial do assinante, histórico recente e risco operacional.
- **FR-BILL-008**: O sistema MUST permitir consulta e filtro por plano, status comercial, vencimento, bloqueio, período e ação operacional relevante.
- **FR-BILL-009**: O sistema MUST registrar eventos operacionais do ciclo comercial para integração com notificações, backbone e processos financeiros correlatos.
- **FR-BILL-010**: O sistema MUST impedir mudanças de estado comercial sem registro de autor, data efetiva e justificativa quando aplicável.
- **FR-BILL-011**: O sistema MUST oferecer visão consolidada de carteira vencida, assinantes em grace period, bloqueios ativos e reativações recentes.
- **FR-BILL-012**: Para esta feature com impacto direto na operação SaaS, a spec MUST definir requisitos de backup, restore validation e rollback para planos, assinaturas, cobranças e estados de bloqueio.

### Key Entities

- **PlanoComercial**: representa a oferta comercial disponível para contratação, renovação ou migração.
- **AssinaturaPlataforma**: representa o contrato ativo ou histórico entre a plataforma e um assinante.
- **FaturaSaaS**: representa a cobrança emitida para um período comercial da assinatura.
- **PoliticaInadimplencia**: representa as regras de tolerância, suspensão, bloqueio e reativação aplicáveis à assinatura.
- **EventoComercialAssinante**: representa ações rastreáveis como mudança de plano, vencimento, bloqueio, desbloqueio, cancelamento e reativação.

## Success Criteria

### Measurable Outcomes

- **SC-BILL-001**: 100% das assinaturas ativas possuem plano, vigência, status comercial e histórico mínimo de alterações rastreáveis.
- **SC-BILL-002**: A plataforma consegue identificar assinantes elegíveis para bloqueio em menos de 1 minuto após a atualização do estado financeiro.
- **SC-BILL-003**: Reativação de assinante regularizado pode ser concluída em menos de 3 minutos por operador autorizado.
- **SC-BILL-004**: O painel central permite localizar assinantes por status comercial, plano ou vencimento em até 3 interações.
- **SC-BILL-005**: Toda mudança de bloqueio, desbloqueio, migração de plano ou cancelamento fica auditável com autor, motivo e data efetiva.

## Dependencies

- Módulo `001` para vínculo entre assinante central e tenant provisionado
- Módulo `002` para autorização do super admin e trilha de auditoria
- Módulo `008` para consistência com eventos financeiros e reconciliação de cobrança
- Módulo `010` para publicação de eventos comerciais e visibilidade operacional ponta a ponta
