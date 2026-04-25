# Configuracao de Monitoramento - UptimeRobot e Prometheus

## Objetivo

Este guia descreve como configurar monitoramento externo com UptimeRobot e monitoramento interno com Prometheus, Grafana, Blackbox Exporter e cAdvisor para o ERP BateriaExpert.

## Alvos Criticos

- ERP Core: `/up`
- MS-001 Fiscal: `/api/v1/health` ou endpoint equivalente publicado
- MS-002 Bancario: `/api/v1/health` ou endpoint equivalente publicado
- MS-003 WhatsApp n8n: `/api/v1/health` ou endpoint equivalente publicado
- MS-004 Open Finance: `/api/v1/health` ou endpoint equivalente publicado
- MS-005 Geocoding: `/api/v1/health` ou endpoint equivalente publicado
- PostgreSQL central e tenants
- Redis e filas
- Scheduler e jobs de backup

## Monitoramento Externo com UptimeRobot

Use UptimeRobot para detectar indisponibilidade do ponto de vista externo ao cluster ou servidor.

### Monitores HTTP Recomendados

Crie monitores do tipo HTTP(s):

| Nome | URL | Intervalo | Severidade |
| --- | --- | --- | --- |
| BateriaExpert ERP Core | `https://erp.seudominio.com/up` | 1 a 5 min | Critica |
| Backoffice Admin | `https://erp.seudominio.com/admin/login` | 5 min | Alta |
| Login Tenant | `https://erp.seudominio.com/login` | 5 min | Alta |
| MS-001 Fiscal | `https://ms001.seudominio.com/api/v1/health` | 1 a 5 min | Critica |
| MS-002 Bancario | `https://ms002.seudominio.com/api/v1/health` | 1 a 5 min | Critica |
| MS-003 WhatsApp | `https://ms003.seudominio.com/api/v1/health` | 5 min | Alta |
| MS-004 Open Finance | `https://ms004.seudominio.com/api/v1/health` | 5 min | Alta |
| MS-005 Geocoding | `https://ms005.seudominio.com/api/v1/health` | 5 min | Alta |

### Keyword Monitor

Para rotas HTML como login, crie um monitor de keyword validando um termo esperado na pagina.

Exemplo:

- URL: `https://erp.seudominio.com/login`
- Keyword esperada: `BateriaExpert`
- Condicao: considerar indisponivel quando a keyword esperada nao estiver presente.

### Port Monitor

Quando a infraestrutura permitir acesso controlado por IP, monitore portas publicas essenciais:

- `443` para HTTPS.
- `80` apenas se redirecionar para HTTPS.

Evite expor PostgreSQL, Redis ou portas internas apenas para monitoramento externo.

### Alert Contacts

Configure pelo menos:

- E-mail operacional.
- Canal Slack ou equivalente.
- Contato telefonico para incidentes criticos.

### Status Page

Crie uma status page publica ou privada com:

- ERP Core.
- Servicos fiscais.
- Servicos bancarios.
- Notificacoes.
- Open Finance.
- Geocoding.

## Monitoramento Interno com Prometheus

O repositorio ja inclui:

- [docker-compose.monitoring.yml](./docker-compose.monitoring.yml)
- [docker/monitoring/prometheus.yml](./docker/monitoring/prometheus.yml)
- [docker/monitoring/alert-rules.yml](./docker/monitoring/alert-rules.yml)
- Dashboards Grafana em `docker/monitoring/grafana-dashboards/`

## Subindo o Stack de Monitoramento

Garanta que a rede Docker principal ja exista, depois execute:

```bash
docker compose -f docker-compose.monitoring.yml up -d
```

Servicos publicados:

- Prometheus: `http://localhost:9090`
- Grafana: `http://localhost:3000`
- Blackbox Exporter: `http://localhost:9115`
- cAdvisor: `http://localhost:8088`

Credenciais padrao do Grafana no compose local:

```text
Usuario: admin
Senha: admin
```

Altere a senha em ambientes compartilhados ou produtivos.

## Jobs Prometheus Configurados

O arquivo `docker/monitoring/prometheus.yml` coleta:

- `prometheus`
- `cadvisor`
- `blackbox_exporter`
- `erp_http_health`
- `microservices_http_health`
- `infra_tcp_health`

O `blackbox-exporter` valida HTTP 2xx para ERP e microservicos e conexao TCP para dependencias internas.

## Validando Prometheus

Abra:

```text
http://localhost:9090/targets
```

Todos os targets esperados devem aparecer como `UP`.

Queries uteis:

```promql
probe_success
probe_duration_seconds
up
container_memory_working_set_bytes
rate(container_cpu_usage_seconds_total[5m])
```

## Validando Grafana

Abra:

```text
http://localhost:3000
```

Confirme:

- Datasource Prometheus provisionado.
- Dashboard ERP Core carregado.
- Dashboard Microservices carregado.
- Paineis com dados recentes.

## Alertas Prometheus

O arquivo `docker/monitoring/alert-rules.yml` ja cobre:

- ERP Core indisponivel.
- ERP Core com latencia elevada.
- Microservico indisponivel.
- Microservico com latencia elevada.
- Dependencia TCP indisponivel.
- Stack de monitoramento indisponivel.
- CPU elevada em containers.
- Memoria elevada em containers.

Para integrar notificacoes, configure Alertmanager e conecte o Prometheus ao Alertmanager.

## Smoke Test Operacional

Execute:

```bash
./healthcheck.sh
```

Personalize URLs quando necessario:

```bash
export ERP_URL=https://erp.seudominio.com
export MS001_URL=https://ms001.seudominio.com/api/v1/health
export MS002_URL=https://ms002.seudominio.com/api/v1/health
export MS003_URL=https://ms003.seudominio.com/api/v1/health
export MS004_URL=https://ms004.seudominio.com/api/v1/health
export MS005_URL=https://ms005.seudominio.com/api/v1/health

./healthcheck.sh
```

## Referencias Oficiais

- UptimeRobot monitor types: https://help.uptimerobot.com/en/articles/11358441-uptimerobot-monitor-types-explained-http-ping-port-keyword-monitoring
- Prometheus alerting overview: https://prometheus.io/docs/alerting/latest/overview/
- Prometheus alerting rules: https://prometheus.io/docs/prometheus/3.5/configuration/alerting_rules/
