# Checklist Pos-Deploy - ERP BateriaExpert

## Objetivo

Padronizar as verificacoes obrigatorias apos cada deploy do ERP BateriaExpert, reduzindo risco de indisponibilidade, regressao funcional ou perda de observabilidade.

## Antes de Comecar

- Identifique versao, branch, commit e responsavel pelo deploy.
- Confirme janela de deploy aprovada.
- Confirme existencia de backup recente.
- Confirme plano de rollback.
- Confirme canal de comunicacao operacional ativo.

## 1. Validacao de Infraestrutura

- Containers ou pods principais estao em execucao.
- ERP Core responde em `/up`.
- PostgreSQL central aceita conexoes.
- Bancos tenant criticos aceitam conexoes.
- Redis aceita conexoes.
- Workers de fila estao ativos.
- Scheduler esta ativo.
- Volumes persistentes estao montados.
- Certificados TLS estao validos.

Comandos uteis:

```bash
docker compose ps
./healthcheck.sh
php artisan tenant:health --json
```

## 2. Validacao de Aplicacao Laravel

- `.env` ou secrets carregados corretamente.
- `APP_KEY` presente.
- `APP_ENV` correto.
- `APP_DEBUG=false` em producao.
- Cache de configuracao atualizado.
- Rotas carregadas.
- Migrations aplicadas.
- Filas sem backlog critico.

Comandos uteis:

```bash
php artisan about
php artisan migrate:status
php artisan route:list --compact
php artisan queue:failed
```

## 3. Validacao Frontend

- Assets Vite publicados.
- Manifest encontrado em `public/build/manifest.json`.
- Login renderiza sem erro.
- Dashboard tenant renderiza sem erro.
- Dashboard admin renderiza sem erro, quando aplicavel.
- Graficos Chart.js carregam nas telas financeiras e de estoque.

Comandos uteis:

```bash
npm run build
```

Se ocorrer erro de manifest Vite, rode build novamente ou valide o pipeline de assets.

## 4. Validacao de Autenticacao e RBAC

- Usuario admin consegue acessar backoffice.
- Usuario gestor consegue acessar dashboard financeiro.
- Usuario vendedor consegue acessar componentes de Vales.
- Usuario estoquista consegue acessar dashboard de estoque.
- Usuario sem permissao nao enxerga modulos restritos.
- Logout funciona.

## 5. Validacao de Fluxos Criticos

### Vendas e Vales

- Criar Vale com cliente.
- Buscar bateria por SKU ou marca.
- Selecionar bateria.
- Validar Net Price em tempo real.
- Adicionar item.
- Visualizar Vale na listagem.
- Faturar Vale em ambiente controlado.
- Cancelar Vale de teste e validar estorno de reserva.

### Estoque

- Consultar saldo por deposito.
- Validar produtos em alerta.
- Validar grafico de saidas por periodo.
- Validar produtos mais vendidos.

### Financeiro

- Validar cards de a receber, a pagar e margem media.
- Validar grafico de fluxo de caixa.
- Validar ultimas transacoes.
- Validar pendencias de conciliacao.

## 6. Validacao de Tenancy

- Tenant ativo aparece em `tenant:list`.
- Tenant trial aparece quando aplicavel.
- Tenant inativo nao acessa operacao.
- Banco tenant correto e selecionado.
- Dados de um tenant nao aparecem em outro.

Comandos:

```bash
php artisan tenant:list --status=active
php artisan tenant:health --json
```

## 7. Validacao de Microservicos

- MS-001 Fiscal responde healthcheck.
- MS-002 Bancario responde healthcheck.
- MS-003 WhatsApp n8n responde healthcheck.
- MS-004 Open Finance responde healthcheck.
- MS-005 Geocoding responde healthcheck.
- Logs nao mostram erro recorrente apos deploy.

Comando:

```bash
./healthcheck.sh
```

## 8. Validacao de Monitoramento

- UptimeRobot mostra monitores como UP.
- Prometheus targets estao UP.
- Grafana recebe dados recentes.
- Alertas esperados estao inativos.
- Alertmanager ou canal equivalente recebe teste de notificacao.

URLs locais:

```text
Prometheus: http://localhost:9090/targets
Grafana: http://localhost:3000
```

## 9. Validacao de Logs

Verifique:

- `storage/logs/laravel.log`
- Logs dos containers.
- Logs de workers.
- Logs de scheduler.
- Logs dos microservicos.

Sinais de alerta:

- Exceptions repetidas.
- Erros 500.
- Falhas de conexao com banco.
- Falhas de autenticacao inesperadas.
- Jobs falhando em lote.

## 10. Testes Pos-Deploy

Execute no minimo:

```bash
php artisan test --compact
```

Quando o ambiente nao permitir suite completa em producao, execute em homologacao com o mesmo build e rode smoke tests em producao.

## 11. Go/No-Go

Deploy pode ser aceito quando:

- Healthchecks estao OK.
- Fluxos criticos passaram.
- Monitoramento esta ativo.
- Logs sem erros criticos.
- Backup pre-deploy confirmado.
- Responsavel operacional aprovou.

Deploy deve ser revertido quando:

- ERP Core fica indisponivel.
- Login falha para perfis principais.
- Migrations deixam tenants indisponiveis.
- Fluxos de faturamento, estoque ou financeiro quebram.
- Alertas criticos persistem apos 10 minutos.

## Registro Final

Preencha no canal operacional:

```text
Deploy:
Versao:
Commit:
Inicio:
Fim:
Responsavel:
Backup validado:
Healthcheck:
Smoke tests:
Monitoramento:
Decisao: aprovado/revertido
Observacoes:
```
