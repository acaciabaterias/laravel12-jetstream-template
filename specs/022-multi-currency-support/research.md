# Research: Módulo 022 - Multi-Currency Support

## Decision 1: Preservar `BRL` como valor base e tratar múltiplas moedas como projeção central

**Rationale**: Os módulos `011` a `021` já operam com montantes persistidos em moeda única. Preservar `BRL` como base evita reescrever histórico financeiro, relatórios e contratos internos, enquanto a camada de projeção entrega leitura monetária convertida por operador.

**Alternatives considered**:
- Persistir todas as moedas por registro operacional: rejeitado por expandir demais o escopo e criar risco de regressão ampla.
- Converter valores in-place durante publicação: rejeitado por perder comparabilidade histórica e auditoria.

## Decision 2: Publicar moedas suportadas e taxas como pacote governado e versionado

**Rationale**: O padrão dos módulos `018`, `020` e `021` já usa publicação ativa, inspeção e rollback. Repetir essa estratégia para moedas reduz risco operacional e mantém coerência com a governança central.

**Alternatives considered**:
- Configuração fixa em arquivo: rejeitada por não oferecer histórico nem rollback.
- Taxas isoladas por moeda sem pacote versionado: rejeitada por dificultar consistência de comparação entre moedas na mesma janela.

## Decision 3: Validar cobertura mínima por conjunto obrigatório de conversões centrais

**Rationale**: O recorte mínimo precisa garantir que billing, payments, recovery, analytics e reports consigam renderizar moedas suportadas sem lacunas ou taxa ausente. Uma lista governada de conversões obrigatórias permite inspeção objetiva e rápida.

**Alternatives considered**:
- Aceitar qualquer moeda sem checar taxa: rejeitada por expor painéis a valores vazios ou incorretos.
- Validar apenas no frontend: rejeitada por deslocar regra crítica para uma camada menos auditável.

## Decision 4: Persistir preferência monetária por operador da plataforma

**Rationale**: O valor de múltiplas moedas aparece primeiro no plano central. Persistir a preferência por operador replica a ergonomia entregue no módulo `021` para idioma e mantém a experiência consistente entre operadores financeiros, suporte e super admin.

**Alternatives considered**:
- Preferência global única: rejeitada por não atender equipes com necessidades distintas.
- Preferência apenas em sessão: rejeitada por não sobreviver a novas autenticações e dificultar suporte.

## Decision 5: Publicar eventos materiais no backbone `010`

**Rationale**: Publicação e rollback de tabela monetária podem afetar relatórios, cobrança e análise comercial. Propagar esses eventos no backbone mantém observabilidade e readiness para consumidores centrais.

**Alternatives considered**:
- Não publicar evento: rejeitada por reduzir rastreabilidade operacional.
- Publicar somente log local: rejeitada por não integrar com o catálogo já consolidado do backbone.
