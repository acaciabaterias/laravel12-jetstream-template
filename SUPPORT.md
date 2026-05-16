# Support

## Quando Usar Este Guia

Use este documento para entender qual canal seguir para:

- bugs
- duvidas de uso
- operacao e deploy
- suporte de infraestrutura
- incidentes de seguranca

## 1. Bugs e Defeitos

Use o rastreador de issues do repositorio para:

- bugs reproduziveis
- regressões
- falhas de build ou testes
- problemas de instalacao
- comportamento inesperado em modulos ou microservicos

Ao abrir a issue, inclua:

- commit ou branch
- ambiente
- modulo ou servico afetado
- passos para reproduzir
- comportamento esperado
- comportamento atual
- logs, screenshots ou payloads relevantes

Use os templates em `.github/ISSUE_TEMPLATE`.

## 2. Dúvidas de Uso e Operacao

Use o fluxo interno da sua equipe ou o canal operacional definido pela organizacao para:

- onboarding
- deploy
- configuracao local
- leitura de specs e arquitetura
- uso de modulos do ERP
- operacao dos microservicos

Antes de pedir ajuda, consulte:

- [README.md](./README.md)
- [ARCHITECTURE.md](./ARCHITECTURE.md)
- [MICROSERVICES.md](./MICROSERVICES.md)
- [API_GUIDE.md](./API_GUIDE.md)
- [DEPLOYMENT_DETAILED.md](./DEPLOYMENT_DETAILED.md)
- [TROUBLESHOOTING.md](./TROUBLESHOOTING.md)
- [PERFORMANCE.md](./PERFORMANCE.md)

## 3. Vulnerabilidades

Nao use issues publicas.

Siga o fluxo privado descrito em [SECURITY.md](./SECURITY.md).

## 4. Suporte de Infraestrutura

Para problemas relacionados a:

- Docker
- PostgreSQL
- Redis
- Supabase
- Proxmox
- healthchecks
- workers e scheduler

consulte primeiro:

- [DEPLOY_PRODUCAO.md](./DEPLOY_PRODUCAO.md)
- [DEPLOY_PROXMOX.md](./DEPLOY_PROXMOX.md)
- [DEPLOY_SUPABASE.md](./DEPLOY_SUPABASE.md)
- [DEPLOYMENT_DETAILED.md](./DEPLOYMENT_DETAILED.md)
- [POSTGRESQL_LOCAL_SETUP.md](./POSTGRESQL_LOCAL_SETUP.md)

## 5. SLA Interno Recomendado

- incidente critico: triagem imediata
- bug funcional: proxima janela de manutencao ou hotfix
- duvida operacional: conforme fila e prioridade do time
- documentacao: conforme backlog de manutencao

## Antes de Pedir Ajuda

Valide o basico primeiro:

```bash
php artisan test --compact
docker compose config
./healthcheck.sh
./check-pg.sh
```

Se o problema for frontend ou assets:

```bash
npm run build
```

Se o problema for de cache/configuracao:

```bash
php artisan config:clear
php artisan cache:clear
```

## Documentos Relacionados

- contribuicao: [CONTRIBUTING.md](./CONTRIBUTING.md)
- seguranca: [SECURITY.md](./SECURITY.md)
- conduta: [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md)
