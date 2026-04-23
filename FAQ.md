# FAQ

## Visao Geral

Este FAQ responde as duvidas mais comuns sobre instalacao, operacao, contribuicao e microservicos do ERP BateriaExpert.

Estado atual do projeto:

- `136` testes passando
- `9` modulos core implementados
- `5` microservicos scaffoldados
- stack com Docker e `docker-compose.yml`

## Instalacao e Configuracao

### Como subir o ambiente pela primeira vez?

O caminho mais rapido e:

1. clonar o repositorio
2. copiar os arquivos `.env`
3. instalar dependencias
4. subir os servicos
5. rodar migrations e seeders

Fluxo local sem Docker:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --database=central --path=database/migrations/central --no-interaction
php artisan db:seed --class=PlanosSeeder --no-interaction
php artisan db:seed --class=SuperAdminSeeder --no-interaction
composer run dev
```

Fluxo com Docker:

```bash
docker compose up -d --build
docker compose ps
./healthcheck.sh
```

Consulte tambem:

- [README.md](./README.md)
- [DEPLOYMENT_DETAILED.md](./DEPLOYMENT_DETAILED.md)
- [MICROSERVICES.md](./MICROSERVICES.md)

### Como rodar os testes?

Para rodar a suite completa:

```bash
php artisan test --compact
```

Para rodar um arquivo especifico:

```bash
php artisan test --compact tests/Feature/ExampleTest.php
```

Para formatar o codigo:

```bash
vendor/bin/pint --dirty --format agent
```

### Preciso do PostgreSQL local?

Depende do modo de execucao.

- com Docker: nao necessariamente, porque o `docker-compose.yml` sobe os bancos do stack
- sem Docker: sim, voce precisa de PostgreSQL acessivel para o banco central e, dependendo do fluxo, para bancos tenant

Se estiver trabalhando sem containers, use:

- [POSTGRESQL_LOCAL_SETUP.md](./POSTGRESQL_LOCAL_SETUP.md)
- `./check-pg.sh`

## Uso e Funcionalidades

### Como criar o primeiro Super Admin?

O caminho esperado hoje e via seeder inicial:

```bash
php artisan db:seed --class=SuperAdminSeeder --no-interaction
```

Se o ambiente ja tiver sido provisionado, revise tambem os seeders de demonstracao e a configuracao do banco central.

### Como criar um novo tenant (cliente)?

O projeto usa arquitetura `database-per-client`. Em alto nivel, o fluxo e:

1. cadastrar o cliente no banco central
2. configurar metadados de conexao do tenant
3. provisionar o banco do tenant
4. rodar migrations tenant

Quando aplicavel:

```bash
php artisan tenant:migrate-all --force
```

Documentos uteis:

- [ARCHITECTURE.md](./ARCHITECTURE.md)
- [DEPLOY_SUPABASE.md](./DEPLOY_SUPABASE.md)
- [DEPLOYMENT_DETAILED.md](./DEPLOYMENT_DETAILED.md)

### Como acessar como Super Admin?

O projeto possui area administrativa separada por rotas `admin.*`. O acesso depende de:

- usuario com perfil ou permissao apropriada
- seed inicial executado
- ambiente configurado corretamente

Depois do seeder, use a URL e as credenciais definidas no ambiente de demonstracao ou nos dados seedados. Se nao tiver certeza das credenciais atuais, revise os seeders do projeto antes de alterar dados manualmente.

### O que e o Net Price?

No contexto do BateriaExpert, o Net Price e o preco liquido da bateria considerando a regra de negocio da sucata.

Em termos praticos:

- se houver devolucao de sucata, o preco segue a composicao normal
- se nao houver devolucao, o sistema aplica acrescimo com base na tabela de peso e na logica de credito de sucata

Essa regra aparece nos fluxos de venda e nos servicos de calculo do dominio.

## Solucao de Problemas

### Erro "Connection refused" ao subir

Esse erro normalmente indica que algum servico dependente nao esta acessivel.

Verifique:

- PostgreSQL
- Redis
- containers do Docker
- host e porta no `.env`
- URLs internas dos microservicos

Comandos uteis:

```bash
docker compose ps
./check-pg.sh
./healthcheck.sh
```

Se estiver sem Docker, confirme se banco e Redis estao de fato ouvindo nas portas configuradas.

### Erro de migrations

As causas mais comuns sao:

- conexao de banco incorreta
- schema ou banco inexistente
- ordem incorreta entre banco central e tenant
- tabela parcialmente criada em tentativa anterior

Para o banco central:

```bash
php artisan migrate --database=central --path=database/migrations/central --no-interaction
```

Para tenants:

```bash
php artisan tenant:migrate-all --force
```

Se o erro persistir, revise:

- credenciais no `.env`
- estrutura esperada em `database/migrations/central`
- estrutura esperada em `database/migrations/tenant`

### Como ver logs do sistema?

Para o ERP Core, comece por:

- `storage/logs/laravel.log`

Se estiver usando Docker:

```bash
docker compose logs --tail=100
```

Para microservicos, veja os logs do servico correspondente ou os logs do container especifico.

### Healthcheck falha

O script de healthcheck valida:

- `ERP Core` em `/up`
- microservicos em `/api/v1/health`

Execute:

```bash
./healthcheck.sh
```

Se falhar, teste endpoint por endpoint:

```bash
curl -i http://localhost:8000/up
curl -i http://localhost:8001/api/v1/health
curl -i http://localhost:8002/api/v1/health
curl -i http://localhost:8003/api/v1/health
curl -i http://localhost:8004/api/v1/health
curl -i http://localhost:8005/api/v1/health
```

As causas mais comuns sao:

- servico nao iniciado
- porta diferente da esperada
- problema de proxy
- `.env` faltando ou incorreto

## Contribuicao

### Como reportar um bug?

Abra uma issue usando o template apropriado em:

- `.github/ISSUE_TEMPLATE/bug_report.md`

Inclua:

- ambiente
- branch ou commit
- modulo afetado
- passos para reproduzir
- comportamento esperado
- comportamento atual
- logs e evidencias

Leia tambem [SUPPORT.md](./SUPPORT.md).

### Como sugerir uma feature?

Abra uma issue usando:

- `.github/ISSUE_TEMPLATE/feature_request.md`

Explique:

- problema de negocio ou oportunidade
- solucao sugerida
- impacto esperado
- criterios de aceite

### Quais os padroes de codigo?

Os principais padroes do projeto sao:

- Laravel 12
- arquitetura tenant-aware com `database-per-client`
- preferencia por Form Requests, Policies, Jobs, Events e Services
- Livewire para interfaces reativas
- testes obrigatorios para mudancas relevantes
- formatacao com Pint

Documentos principais:

- [CONTRIBUTING.md](./CONTRIBUTING.md)
- [ARCHITECTURE.md](./ARCHITECTURE.md)
- [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md)

## Microservicos

### Como testar um microservico isoladamente?

Cada microservico possui rotas proprias em `/api/v1/...`, testes dedicados e endpoint de health.

Exemplo para o `MS-001`:

```bash
curl -i http://localhost:8001/api/v1/health
php artisan test --compact microservicos/ms-001-fiscal-acbr/tests/Feature/FiscalMsTest.php
```

Exemplo para o `MS-005`:

```bash
curl -i http://localhost:8005/api/v1/health
php artisan test --compact microservicos/ms-005-geocoding/tests/Feature/GeocodingMsTest.php
```

Consulte:

- [MICROSERVICES.md](./MICROSERVICES.md)
- [API_GUIDE.md](./API_GUIDE.md)

### Os microservicos sao obrigatorios?

Nao em todos os cenarios de desenvolvimento inicial, mas eles sao parte planejada da arquitetura do produto.

Na pratica:

- o ERP Core concentra o dominio principal
- os microservicos isolam integracoes especializadas, como fiscal, bancario, notificacoes, Open Finance e geocoding
- para fluxos completos de producao, a presenca deles e recomendada
- para desenvolvimento localizado, alguns fluxos podem usar mocks ou homologacao parcial

Se voce estiver validando apenas uma parte do ERP, pode trabalhar sem todos os microservicos ativos, desde que o fluxo em questao nao dependa diretamente deles.

## Referencias

- [README.md](./README.md)
- [ARCHITECTURE.md](./ARCHITECTURE.md)
- [MICROSERVICES.md](./MICROSERVICES.md)
- [API_GUIDE.md](./API_GUIDE.md)
- [TROUBLESHOOTING.md](./TROUBLESHOOTING.md)
- [CONTRIBUTING.md](./CONTRIBUTING.md)
- [SUPPORT.md](./SUPPORT.md)
