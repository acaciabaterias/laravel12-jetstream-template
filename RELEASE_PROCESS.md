# Release Process

## Objetivo

Este guia define o processo de release do ERP BateriaExpert, cobrindo versionamento, fluxo de branches, CI/CD, checklist operacional, hotfixes e consideracoes para producao.

Ele deve ser usado junto com:

- [CHANGELOG.md](./CHANGELOG.md)
- [DEPLOYMENT_DETAILED.md](./DEPLOYMENT_DETAILED.md)
- [DEPLOY_PRODUCAO.md](./DEPLOY_PRODUCAO.md)
- [DEPLOY_PROXMOX.md](./DEPLOY_PROXMOX.md)
- [SUPPORT.md](./SUPPORT.md)

## 1. Versionamento

O projeto segue Semantic Versioning.

Formato:

```text
MAJOR.MINOR.PATCH
```

### Regras

- `MAJOR`: mudancas incompatíveis, alteracoes estruturais relevantes ou quebra de contratos
- `MINOR`: novas funcionalidades compativeis com versoes anteriores
- `PATCH`: correcoes, ajustes pequenos, docs e refinamentos sem quebra de compatibilidade

### Exemplos

- `1.0.0`: primeiro release pronto para lancamento
- `1.1.0`: novas capacidades sem quebra de compatibilidade
- `1.1.1`: hotfix ou correcao pontual de producao
- `2.0.0`: mudancas incompatíveis ou nova fase arquitetural

## 2. Ciclo de Release

Fluxo padrao:

```text
feature branch -> develop -> release candidate -> main
```

### Etapas

#### 1. Feature branch

Toda mudanca deve nascer em uma branch de trabalho, por exemplo:

- `feat/tenant-dashboard`
- `fix/fiscal-contingency-retry`
- `docs/release-process`

Objetivo:

- desenvolver mudancas pequenas e coesas
- validar testes relacionados
- atualizar documentacao impactada

#### 2. Develop

A branch `develop` consolida features prontas para o proximo ciclo.

Objetivo:

- integrar funcionalidades aprovadas
- validar compatibilidade entre modulos
- manter ambiente de homologacao em estado utilizavel

#### 3. Release candidate

Quando um conjunto de funcionalidades estiver pronto para validacao final, cria-se uma branch de release, por exemplo:

- `release/1.1.0`
- `release/1.2.0-rc1`

Objetivo:

- congelar escopo
- executar validacao final
- ajustar documentacao, changelog e versao
- rodar smoke tests e checklist operacional

#### 4. Main

A branch `main` representa o estado liberado para producao.

Objetivo:

- conter apenas releases aprovadas
- servir de referencia para tags oficiais
- alimentar o deploy produtivo

## 3. CI/CD Pipeline

O projeto utiliza GitHub Actions em `.github/workflows`.

Workflows principais:

- `test.yml`
- `lint.yml`
- `deploy.yml`

### Pipeline esperado

#### Em feature branch e develop

- checkout do codigo
- install de dependencias
- execucao de testes automatizados
- validacao de estilo e lint

#### Em release candidate

- execucao completa de testes relevantes
- revisao de `CHANGELOG.md`
- revisao de documentacao de release
- validacao de deploy

#### Em main

- workflow de deploy manual ou controlado por ambiente
- registro da release
- aplicacao do checklist final

### Validacoes recomendadas

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
docker compose config
./healthcheck.sh
```

## 4. Checklist de Release

Antes de promover um release para `main`, confirme:

- [ ] escopo congelado e aprovado
- [ ] `CHANGELOG.md` atualizado
- [ ] versao do release definida
- [ ] testes automatizados passando
- [ ] documentacao impactada atualizada
- [ ] migrations revisadas
- [ ] impacto em banco central e banco tenant validado
- [ ] impacto em microservicos revisado
- [ ] workers, scheduler e healthchecks considerados
- [ ] rollback planejado

### Checklist tecnico

- [ ] `php artisan test --compact`
- [ ] `vendor/bin/pint --dirty --format agent`
- [ ] `docker compose config`
- [ ] `./healthcheck.sh`
- [ ] build de frontend validado
- [ ] variaveis de ambiente revisadas

### Checklist funcional

- [ ] login administrativo
- [ ] resolucao de tenant
- [ ] sync mobile
- [ ] emissao fiscal mock ou homologada
- [ ] cobranca bancaria
- [ ] notificacao via WhatsApp
- [ ] captura Open Finance
- [ ] rota e ETA no geocoding

## 5. Hotfix Process

Hotfixes devem ser reservados para incidentes ou falhas relevantes em producao.

Fluxo recomendado:

```text
main -> hotfix/x.y.z -> main -> develop
```

### Passos

1. crie uma branch a partir de `main`
2. implemente apenas a correcao necessaria
3. rode os testes minimos relacionados
4. atualize `CHANGELOG.md`
5. faça merge em `main`
6. gere a nova tag `PATCH`
7. replique o merge em `develop`

### Exemplo

```text
main -> hotfix/1.1.1 -> main -> develop
```

### Regras de hotfix

- nao misturar feature nova com hotfix
- manter o diff pequeno
- priorizar baixo risco operacional
- documentar causa, impacto e mitigacao

## 6. Ambiente de Producao

O projeto considera producao com Proxmox e Docker como abordagem recomendada de operacao.

### Topologia sugerida

- `vm-app-01`: ERP Core
- `vm-db-01`: PostgreSQL central
- `vm-ms-01`: microservicos
- `vm-edge-01`: proxy reverso

### Componentes principais

- ERP Core Laravel 12
- PostgreSQL central
- bancos tenant dedicados
- Redis para filas e cache
- microservicos `MS-001` a `MS-005`
- reverse proxy com SSL

### Deploy com Docker

Fluxo basico:

```bash
docker compose up -d --build
docker compose ps
./healthcheck.sh
```

### Deploy em Proxmox

Fluxo recomendado:

1. atualizar codigo
2. instalar dependencias
3. aplicar migrations
4. reiniciar workers
5. validar endpoints

Consulte:

- [DEPLOY_PROXMOX.md](./DEPLOY_PROXMOX.md)
- [DEPLOYMENT_DETAILED.md](./DEPLOYMENT_DETAILED.md)

## 7. Tagging e Registro de Release

Depois de aprovar o release:

1. atualizar `CHANGELOG.md`
2. garantir merge em `main`
3. criar a tag da versao
4. publicar notas de release

Exemplo:

```bash
git tag v1.1.0
git push origin v1.1.0
```

As notas devem resumir:

- funcionalidades adicionadas
- correcoes relevantes
- mudancas operacionais
- impacto em deploy ou upgrade

## 8. Rollback

Todo release precisa de plano de rollback.

### Minimo esperado

- release anterior identificada
- backup do banco realizado
- imagem ou build anterior disponivel
- procedimento para restaurar servicos
- healthcheck de retorno documentado

### Cuidados

- rollback de banco exige cautela adicional
- migrations destrutivas devem ser evitadas sem plano claro
- microservicos e ERP Core devem permanecer com contratos compativeis durante o retorno

## 9. Responsabilidades

### Desenvolvimento

- preparar codigo, testes e documentacao
- revisar impacto tecnico
- apoiar troubleshooting do release

### Revisao

- validar escopo
- revisar risco
- aprovar merge e versao

### Operacao

- executar deploy
- validar healthchecks
- acompanhar logs, filas e estabilidade

## Referencias

- [README.md](./README.md)
- [CHANGELOG.md](./CHANGELOG.md)
- [CONTRIBUTING.md](./CONTRIBUTING.md)
- [DEPLOYMENT_DETAILED.md](./DEPLOYMENT_DETAILED.md)
- [DEPLOY_PRODUCAO.md](./DEPLOY_PRODUCAO.md)
- [DEPLOY_PROXMOX.md](./DEPLOY_PROXMOX.md)
