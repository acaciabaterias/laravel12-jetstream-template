# Research: Módulo 015 - Production Observability Assurance

## Decisões principais

### 1. Observabilidade mínima não dependerá exclusivamente de Grafana
- **Decision**: O módulo manterá leitura operacional mínima dentro do próprio ERP, com dashboards Livewire e inspeção JSON, mesmo quando Grafana/Prometheus forem camadas externas preferenciais.
- **Rationale**: A operação precisa de fallback controlado e auditável dentro do produto.
- **Alternatives considered**:
  - depender só de Grafana: rejeitado por fragilidade operacional quando há indisponibilidade parcial do stack de monitoramento

### 2. Severidade operacional será derivada de regras explícitas
- **Decision**: Severidade será classificada com base em backlog, latência, falha, replay pendente, exceção financeira e indisponibilidade de coletor.
- **Rationale**: Reduz ambiguidade e padroniza escalonamento.
- **Alternatives considered**:
  - severidade manual por operador: rejeitado por inconsistência e atraso de resposta

### 3. Baseline de carga será persistido por cenário crítico
- **Decision**: Cada cenário crítico terá baseline próprio com throughput, latência, taxa de falha e observações de ambiente.
- **Rationale**: Permite comparar regressão por fluxo e não apenas visão agregada.
- **Alternatives considered**:
  - um único baseline global: rejeitado por esconder gargalos localizados

### 4. Runbook exige evidência de execução
- **Decision**: Replay, rollback, restore validation e contingência só serão considerados concluídos com evidência persistida.
- **Rationale**: Fecha a lacuna entre procedimento escrito e operação real.
- **Alternatives considered**:
  - registrar apenas status textual: rejeitado por pouca auditabilidade

### 5. Eventos operacionais materiais entram no backbone `010`
- **Decision**: degradação persistente, incidente aberto e recuperação de serviço publicarão eventos centrais versionados.
- **Rationale**: Mantém rastreabilidade transversal com os demais módulos.
- **Alternatives considered**:
  - logs locais sem eventos: rejeitado por isolamento excessivo do sinal operacional
