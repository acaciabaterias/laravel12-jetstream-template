# Regras de Alerta Operacional - ERP BateriaExpert

## Objetivo

Definir regras de alerta para operacao do BateriaExpert ERP, cobrindo disponibilidade, performance, banco de dados, filas, backups, microservicos e seguranca operacional.

## Severidades

| Severidade | Definicao | Tempo de resposta |
| --- | --- | --- |
| Critica | Servico indisponivel, perda de dados, faturamento bloqueado ou risco de seguranca | Imediato |
| Alta | Degradacao relevante, fluxo critico parcial ou dependencia instavel | Ate 30 min |
| Media | Problema funcional com workaround ou risco operacional crescente | Ate 4 h |
| Baixa | Ajuste preventivo, capacidade ou melhoria operacional | Proxima janela |

## Canais

- Critica: telefone/on-call + chat operacional + e-mail.
- Alta: chat operacional + e-mail.
- Media: ticket + chat.
- Baixa: backlog operacional.

## Alertas Ja Configurados no Prometheus

O arquivo [docker/monitoring/alert-rules.yml](./docker/monitoring/alert-rules.yml) ja define:

| Alerta | Condicao | Severidade |
| --- | --- | --- |
| `ErpCoreDown` | `probe_success{job="erp_http_health"} == 0` por 2 min | Critica |
| `ErpCoreHighLatency` | media de `probe_duration_seconds` acima de 1.5s por 5 min | Warning |
| `MicroserviceDown` | healthcheck HTTP de microservico falha por 2 min | Critica |
| `MicroserviceHighLatency` | latencia media de microservico acima de 1.5s por 5 min | Warning |
| `InfrastructureTcpTargetDown` | dependencia TCP indisponivel por 2 min | Critica |
| `MonitoringStackTargetDown` | Prometheus, cAdvisor ou Blackbox indisponivel por 2 min | Critica |
| `AppContainerHighCpu` | containers de app acima de 0.8 CPU por 10 min | Warning |
| `AppContainerHighMemory` | containers acima de 512 MiB por 10 min | Warning |

## Alertas Externos Recomendados no UptimeRobot

| Alerta | Tipo | Condicao | Severidade |
| --- | --- | --- | --- |
| ERP Core indisponivel | HTTP(s) | `/up` nao retorna sucesso | Critica |
| Login tenant indisponivel | HTTP(s) ou keyword | `/login` nao responde ou nao contem keyword esperada | Alta |
| Backoffice indisponivel | HTTP(s) ou keyword | `/admin/login` nao responde ou nao contem keyword esperada | Alta |
| TLS expirando | SSL | certificado proximo do vencimento | Alta |
| Dominio expirando | Domain | dominio proximo do vencimento | Alta |
| MS Fiscal indisponivel | HTTP(s) | healthcheck falha | Critica |
| MS Bancario indisponivel | HTTP(s) | healthcheck falha | Critica |
| MS WhatsApp indisponivel | HTTP(s) | healthcheck falha | Alta |
| MS Open Finance indisponivel | HTTP(s) | healthcheck falha | Alta |
| MS Geocoding indisponivel | HTTP(s) | healthcheck falha | Media |

## Alertas de Banco de Dados

Configure no provedor PostgreSQL, Supabase ou stack Prometheus quando os exporters estiverem disponiveis:

| Alerta | Condicao sugerida | Severidade |
| --- | --- | --- |
| Banco central indisponivel | conexao falha por 2 min | Critica |
| Banco tenant critico indisponivel | conexao falha por 2 min | Critica |
| Uso de disco alto | acima de 80% por 10 min | Alta |
| Uso de disco critico | acima de 90% por 5 min | Critica |
| Conexoes elevadas | acima de 80% do limite por 10 min | Alta |
| Replicacao atrasada | lag acima do limite operacional | Alta |
| Queries lentas | p95 acima do SLA por 15 min | Media |

## Alertas de Filas e Jobs

| Alerta | Condicao sugerida | Severidade |
| --- | --- | --- |
| Worker parado | processo de queue ausente por 2 min | Critica |
| Jobs falhando | `php artisan queue:failed` retorna falhas novas | Alta |
| Backlog de fila alto | tamanho acima do limite por 10 min | Alta |
| Scheduler parado | tarefas agendadas nao executam na janela esperada | Alta |
| Backup tenant falhou | `tenant:backup --all` nao conclui | Critica |
| Sincronizacao de sucata falhou | job recorrente falha em sequencia | Alta |

## Alertas de Negocio

| Alerta | Condicao sugerida | Severidade |
| --- | --- | --- |
| Faturamento sem sucesso | nenhum Vale faturado em periodo comercial esperado | Media |
| Muitos cancelamentos | taxa de cancelamento acima do padrao | Media |
| Estoque critico | muitos produtos abaixo do minimo | Alta |
| Margem negativa | margem media abaixo de 0% | Alta |
| Conciliacoes pendentes elevadas | pendencias acima do limite operacional | Media |

## Alertas de Seguranca

| Alerta | Condicao sugerida | Severidade |
| --- | --- | --- |
| Muitos logins falhos | volume anormal por usuario ou IP | Alta |
| Acesso admin fora de janela | login admin em horario nao esperado | Media |
| APP_DEBUG ativo em producao | `APP_DEBUG=true` detectado | Critica |
| Alteracao de DNS | registro DNS muda inesperadamente | Critica |
| Certificado invalido | TLS invalido ou expirado | Critica |

## Runbook Resumido

### ERP Core Down

1. Validar UptimeRobot e Prometheus.
2. Executar `./healthcheck.sh`.
3. Verificar containers ou pods.
4. Verificar logs Laravel.
5. Verificar PostgreSQL e Redis.
6. Acionar rollback se indisponibilidade persistir apos 10 minutos.

### Microservice Down

1. Identificar `service_name` no alerta.
2. Verificar healthcheck direto.
3. Verificar container ou pod do microservico.
4. Verificar banco/Redis do microservico.
5. Acionar responsavel do dominio fiscal, bancario, notificacao, Open Finance ou geocoding.

### Backup Falhou

1. Verificar logs do scheduler.
2. Rodar `php artisan tenant:backup --all --pretend`.
3. Confirmar `pg_dump`.
4. Verificar credenciais e espaco em disco.
5. Reexecutar backup manual.
6. Abrir incidente se nao houver backup valido nas ultimas 24 horas.

### Banco Indisponivel

1. Verificar provedor PostgreSQL.
2. Confirmar rede e DNS.
3. Verificar limite de conexoes.
4. Pausar jobs nao criticos.
5. Acionar restore ou failover conforme plano de continuidade.

## Janelas de Silenciamento

Silencie alertas somente quando:

- Houver janela de manutencao aprovada.
- O responsavel estiver identificado.
- O tempo de fim estiver definido.
- O canal operacional tiver sido avisado.

Nunca silencie alertas criticos sem registro de motivo.

## Revisao

Revise estas regras:

- Apos cada incidente critico.
- Apos mudancas de arquitetura.
- A cada release maior.
- Pelo menos uma vez por trimestre.
