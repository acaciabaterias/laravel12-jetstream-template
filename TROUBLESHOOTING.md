# Troubleshooting

## Visao Geral

Este guia cobre problemas recorrentes de ambiente, deploy, banco, filas e integracoes.

## 1. A aplicacao sobe, mas a interface nao reflete mudancas

### Sintoma

- layout antigo
- erro de asset
- estilos quebrados

### Causa comum

- assets do Vite nao foram recompilados

### Solucao

```bash
npm run build
```

Em desenvolvimento:

```bash
npm run dev
```

## 2. Erro de Vite manifest nao encontrado

### Sintoma

- `Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest`

### Solucao

```bash
npm run build
```

Ou, em ambiente local:

```bash
composer run dev
```

## 3. ERP Core nao conecta no PostgreSQL central

### Sintoma

- falha de login
- pagina 500
- migrations falham

### O que verificar

- `DB_CONNECTION=central`
- `DB_CENTRAL_HOST`
- `DB_CENTRAL_PORT`
- `DB_CENTRAL_DATABASE`
- `DB_CENTRAL_USERNAME`
- `DB_CENTRAL_PASSWORD`

### Solucao

```bash
./check-pg.sh
php artisan migrate --database=central --path=database/migrations/central --no-interaction
```

## 4. Tenant nao e resolvido corretamente

### Sintoma

- tenant errado
- erro de conexao tenant
- acessos vazios ou inconsistentes por subdominio

### Causa comum

- `Cliente.subdominio` ausente ou incorreto
- metadados do tenant nao cadastrados
- `TenantConnectionMiddleware` sem dados validos

### Solucao

- validar cadastro do tenant no banco central
- confirmar subdominio resolvido
- revisar variaveis de conexao do tenant
- executar `php artisan tenant:health --json`, se esse comando fizer parte do fluxo operacional usado pelo time

## 5. `./healthcheck.sh` falha nos microservicos

### Sintoma

- um ou mais `[FAIL]`

### Causa comum

- servico parado
- porta incorreta
- proxy nao exposto
- rota errada

### Solucao

Verifique diretamente:

```bash
curl -i http://localhost:8001/api/v1/health
curl -i http://localhost:8002/api/v1/health
curl -i http://localhost:8003/api/v1/health
curl -i http://localhost:8004/api/v1/health
curl -i http://localhost:8005/api/v1/health
```

Se o ambiente usa gateway com reescrita, ajuste tambem as URLs do proxy.

## 6. Worker parado ou filas acumulando

### Sintoma

- jobs nao processam
- notificacoes atrasadas
- integracoes fiscais ou bancarias presas

### Solucao

```bash
php artisan queue:restart
```

Se estiver usando supervisor ou systemd:

```bash
sudo systemctl restart erp-core-queue
sudo systemctl status erp-core-queue
```

### O que revisar

- `QUEUE_CONNECTION=redis`
- Redis acessivel
- processo `queue:work` ativo
- logs do worker

## 7. Scheduler nao executa tarefas

### Sintoma

- comandos agendados nao rodam
- rotina diaria nao dispara

### Solucao

Garantir cron ou processo dedicado:

```bash
php artisan schedule:run --verbose --no-interaction
```

Revise:

- cron do sistema
- container do scheduler
- timezone do servidor

## 8. MS-001 entra sempre em contingencia

### Sintoma

- retorno `status=contingency`

### Causa comum

- driver fiscal indisponivel
- dependencia externa ACBr indisponivel

### Solucao

- validar configuracao do driver
- validar conectividade com o stack fiscal
- revisar fila em `GET /api/v1/contingencia/fila`
- usar driver mock em ambiente de homologacao quando apropriado

## 9. PIX ou boleto nao atualizam status

### Sintoma

- cobranca fica em `pendente`
- webhook nao altera registro

### Causa comum

- webhook nao chegou
- `txid` ou `nosso_numero` nao bate
- banco/provedor configurado incorretamente

### Solucao

- testar `POST /api/v1/webhook/{banco}`
- validar `idempotency_key`, `txid` e `nosso_numero`
- revisar logs do MS-002

## 10. Notificacao WhatsApp nao e enviada

### Sintoma

- mensagem bloqueada
- mensagem agendada e nao enviada

### Causa comum

- numero em blacklist
- fora do horario comercial
- Evolution API ou n8n indisponivel

### Solucao

- consultar `GET /api/v1/blacklist`
- consultar `GET /api/v1/fila`
- validar integrações do MS-003

## 11. Open Finance nao captura transacoes

### Sintoma

- consentimento existe, mas lista de transacoes fica vazia

### Causa comum

- token invalido
- callback OAuth incompleto
- provider incorreto

### Solucao

- revisar `GET /api/v1/consentimentos`
- revisar `GET /api/v1/captura/logs`
- validar chave usada em `services.openfinance.token_key`

## 12. Geocodificacao ou roteirizacao retornam dados ruins

### Sintoma

- coordenadas ruins
- rota inesperada
- ETA inconsistente

### Causa comum

- endereco pobre
- cache antigo
- base operacional inconsistente

### Solucao

- invalidar cache do endereco
- corrigir manualmente geocodificacao
- reprocessar rota com payload mais completo

Exemplos:

```bash
curl -X DELETE http://localhost:8005/api/v1/cache/geocodificacao/HASH
curl -X PUT -H "Content-Type: application/json" -d '{"hash":"HASH","latitude":-23.55,"longitude":-46.63}' http://localhost:8005/api/v1/geocodificar/corrigir
```

## 13. Testes falham so em um ambiente

### Causa comum

- `.env` divergente
- cache de config antigo
- banco sujo
- dependencias diferentes

### Solucao

```bash
php artisan config:clear
php artisan cache:clear
php artisan test --compact
```

## 14. Docker Compose sobe, mas containers reiniciam

### O que verificar

- `.env` faltando
- senha de banco divergente
- volume corrompido
- healthcheck falhando

### Solucao

```bash
docker compose ps
docker compose logs --tail=100
docker compose config
```

Depois, valide:

```bash
./healthcheck.sh
```

## 15. Como coletar contexto antes de abrir issue

Reuna:

- commit ou branch
- ambiente
- endpoint afetado
- payload usado
- log ou stack trace
- resultado esperado
- resultado atual

Use os templates em `.github/ISSUE_TEMPLATE`.

## Referencias

- [SUPPORT.md](./SUPPORT.md)
- [MICROSERVICES.md](./MICROSERVICES.md)
- [DEPLOYMENT_DETAILED.md](./DEPLOYMENT_DETAILED.md)
- [API_GUIDE.md](./API_GUIDE.md)
