# Research: Módulo 012 - Platform Payments and Reconciliation

## Decision 1: Manter pagamentos SaaS e reconciliação no banco central

**Decision**: Toda emissão externa, retorno financeiro, conciliação e exceção operacional do SaaS deve residir exclusivamente no banco central.

**Rationale**: Cobrança SaaS pertence à plataforma e precisa conversar diretamente com `AssinaturaPlataforma`, `FaturaSaaS` e estados comerciais do `011` sem espalhar dados financeiros de plataforma pelos bancos tenant.

**Alternatives considered**:
- Replicar cobrança em bancos tenant: descartado por duplicidade e risco de divergência.
- Resolver pagamento apenas no microserviço sem persistência central suficiente: descartado por baixa auditabilidade e pouca governança operacional.

## Decision 2: Tratar gateway como integração configurável e não como regra embutida

**Decision**: O módulo deve abstrair o provedor de cobrança via configuração e serviços de gateway, preservando o domínio central como fonte de verdade.

**Rationale**: O roadmap já prevê evolução de cobrança SaaS; amarrar a lógica a um único provedor cedo demais aumenta custo de troca e de contingência.

**Alternatives considered**:
- Codificar diretamente um único provedor em regras de domínio: descartado por acoplamento desnecessário.

## Decision 3: Idempotência obrigatória para emissão e webhooks

**Decision**: Emissão externa, callbacks e replay operacional devem usar chaves idempotentes e registrar o processamento de cada retorno.

**Rationale**: O risco principal desse módulo é duplicar cobrança, baixa ou evento comercial por reenvio de gateway, timeout ou reprocessamento humano.

**Alternatives considered**:
- Confiar apenas na unicidade do provedor externo: descartado porque não cobre retries locais nem retornos fora de ordem.
- Resolver duplicidade apenas pelo status atual da fatura: descartado por fragilidade em cenários de corrida.

## Decision 4: Conciliação automática apenas quando o match for seguro

**Decision**: A plataforma deve baixar automaticamente só quando referência, valor e estado esperado forem suficientes; o restante deve virar exceção operacional.

**Rationale**: Baixa agressiva reduz trabalho manual, mas erro de conciliação contamina o estado comercial e a receita.

**Alternatives considered**:
- Exigir análise manual para toda liquidação: descartado por custo operacional alto.
- Baixar automaticamente com critérios frouxos: descartado por risco financeiro.

## Decision 5: Estorno, chargeback e divergência precisam de trilha própria

**Decision**: Eventos pós-liquidação que revertam ou contestem o recebimento devem abrir exceção explícita e não sobrescrever o histórico anterior.

**Rationale**: O recebimento original continua relevante para auditoria, cálculo de impacto comercial e eventual reativação já ocorrida.

**Alternatives considered**:
- Sobrescrever o pagamento como se nunca tivesse ocorrido: descartado por perda de rastreabilidade.

## Decision 6: Impacto comercial deve continuar governado pelo módulo 011

**Decision**: O `012` sinaliza liquidação e divergência; o `011` continua sendo a fonte de verdade para grace period, bloqueio e reativação.

**Rationale**: Isso preserva fronteira clara entre pagamento e estado contratual, evitando duplicação de regras comerciais.

**Alternatives considered**:
- Deixar o `012` reimplementar a máquina de estados comercial: descartado por duplicidade funcional e risco de inconsistência.

## Decision 7: Backbone 010 será o canal para eventos financeiros centrais

**Decision**: Mudanças financeiras relevantes como cobrança emitida, cobrança liquidada, divergência aberta e chargeback confirmado devem ser publicadas no backbone `010`.

**Rationale**: O backbone já entrega replay, observabilidade e contratos versionados; pagamentos centrais precisam da mesma disciplina operacional.

**Alternatives considered**:
- Integrar notificações e analytics diretamente a partir do serviço local: descartado por maior acoplamento e menor rastreabilidade.

## Decision 8: Rollback operacional precisa distinguir reversão técnica de reversão financeira

**Decision**: O runbook do módulo deve diferenciar falha técnica de emissão/retorno versus reversão real de pagamento já confirmado.

**Rationale**: Nem todo rollback de software pode ou deve desfazer um recebimento externo; alguns casos exigem reconciliação corretiva e não restauração cega.

**Alternatives considered**:
- Tratar todo problema com restore direto do banco central: descartado por potencial perda de eventos financeiros legítimos já liquidados no provedor.
