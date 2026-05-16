# Research: Módulo 016 - Backbone Monitoring Consolidation

## Decisões principais

### 1. O ERP continuará como fonte de governança do monitoramento
- **Decision**: O módulo registrará catálogo, readiness e provisão no ERP, enquanto Prometheus e Grafana permanecem como camadas externas de coleta e visualização.
- **Rationale**: Preserva rastreabilidade, rollback e coerência com a governança operacional já modelada no módulo `015`.
- **Alternatives considered**:
  - tratar Grafana como única fonte de verdade: rejeitado por baixa auditabilidade no contexto do produto

### 2. Falha de scrape será um evento de degradação explícito
- **Decision**: Ausência de scrape, exporter offline ou latência de coleta fora de faixa serão tratados como degradação do stack de monitoramento.
- **Rationale**: Evita falso positivo de saúde quando o sistema de observação falha.
- **Alternatives considered**:
  - ignorar indisponibilidade de observabilidade externa: rejeitado por criar silêncio operacional perigoso

### 3. Dashboards e alertas serão versionados por pacote
- **Decision**: A provisão será rastreada por versão de pacote aplicada e ambiente validado.
- **Rationale**: Garante rollback e comparação entre homologação, staging e produção.
- **Alternatives considered**:
  - versionar apenas arquivos soltos sem registro no ERP: rejeitado por pouca governança de ambiente

### 4. Taxonomia seguirá os fluxos dos módulos `010` e `015`
- **Decision**: Fluxos, severidades e nomes de alerta seguirão a convenção já usada internamente.
- **Rationale**: Evita taxonomia paralela entre o painel interno e o stack externo.
- **Alternatives considered**:
  - nomenclatura independente por exporter ou dashboard: rejeitado por aumentar ambiguidade

### 5. Eventos materiais da malha de monitoramento entram no backbone `010`
- **Decision**: degradação do stack, scrape health crítico e rollback material publicarão eventos centrais versionados.
- **Rationale**: Mantém correlação transversal com incidentes e readiness já tratadas no ERP.
- **Alternatives considered**:
  - logs locais sem evento central: rejeitado por isolamento excessivo do sinal operacional
