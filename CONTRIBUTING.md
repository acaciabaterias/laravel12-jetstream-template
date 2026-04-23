# Contributing

## Objetivo

Este projeto concentra o ERP Core em Laravel 12 e os microservicos do ecossistema BateriaExpert em um unico monorepo. Toda contribuicao deve preservar:

- arquitetura `database-per-client`
- separacao entre banco central, banco tenant e microservicos
- consistencia com `README.md`, `ARCHITECTURE.md`, specs e guias operacionais
- qualidade de testes e previsibilidade de deploy

## Antes de Comecar

Leia primeiro, conforme o tipo de alteracao:

- [README.md](./README.md)
- [ARCHITECTURE.md](./ARCHITECTURE.md)
- [MICROSERVICES.md](./MICROSERVICES.md)
- [API_GUIDE.md](./API_GUIDE.md)
- [DEPLOYMENT_DETAILED.md](./DEPLOYMENT_DETAILED.md)
- [TROUBLESHOOTING.md](./TROUBLESHOOTING.md)
- `specs/` da funcionalidade relacionada

## Fluxo Recomendado

1. Crie uma branch pequena e focada.
2. Revise specs, arquitetura e artefatos do modulo afetado.
3. Faça mudancas coesas e evite misturar refactor, feature e docs no mesmo PR sem necessidade.
4. Atualize testes e documentacao impactados.
5. Rode a validacao minima antes de abrir o PR.
6. Abra o PR com contexto, riscos, impacto operacional e passos claros de validacao.

## Padrões de Desenvolvimento

- use os padroes existentes do projeto antes de introduzir novos estilos
- prefira Form Requests, Policies, Jobs, Events e Services antes de logica inline
- siga a organizacao do Laravel 12 adotada no repositorio
- para interfaces reativas, mantenha o padrao Livewire ja utilizado
- preserve endpoints de healthcheck e contratos externos dos microservicos
- nao altere dependencias do projeto sem alinhamento previo

## Regras Arquiteturais Importantes

- nao reintroduza isolamento logico legado como substituto do modelo `database-per-client`
- mantenha clara a fronteira entre banco central e bancos tenant
- alteracoes em microservicos devem refletir, quando necessario, em `openapi.yaml`, `postman_collection.json` e na documentacao correspondente
- endpoints, payloads e fluxos operacionais precisam permanecer consistentes com os guias atuais

## Banco de Dados

- migrations do banco central pertencem a `database/migrations/central`
- migrations tenant pertencem a `database/migrations/tenant`
- migrations devem ser reversiveis sempre que possivel
- toda mudanca de schema precisa considerar impacto em indices, filas, jobs e dados existentes
- se houver impacto em RLS, snapshots SQL ou provisionamento tenant, atualize os artefatos relacionados

## Testes e Validacao

Antes do PR, rode o minimo necessario para provar a mudanca:

```bash
php artisan test --compact
```

Quando aplicavel, rode tambem:

```bash
vendor/bin/pint --dirty --format agent
./healthcheck.sh
docker compose config
```

Se a alteracao for localizada, prefira executar o arquivo ou filtro de teste relacionado.

## Commits

Prefira mensagens descritivas e orientadas a escopo, por exemplo:

- `feat(admin): add tenant management filters`
- `fix(finance): prevent duplicate reconciliation`
- `docs(api): expand mobile sync examples`
- `test(logistics): cover route closing rules`

## Pull Requests

Todo PR deve deixar claro:

- qual problema foi resolvido
- qual abordagem foi escolhida
- quais areas foram afetadas
- quais testes foram executados
- quais riscos operacionais existem

Use o template em `.github/PULL_REQUEST_TEMPLATE.md`.

## Checklist Antes do PR

- [ ] li a documentacao e specs relevantes
- [ ] mantive a arquitetura tenant-aware e `database-per-client`
- [ ] atualizei testes impactados
- [ ] rodei validacoes apropriadas para a mudanca
- [ ] atualizei documentacao impactada
- [ ] nao inclui segredos, credenciais ou dados sensiveis

## Issues, Suporte e Seguranca

- bugs e melhorias: use os templates em `.github/ISSUE_TEMPLATE`
- duvidas operacionais: consulte [SUPPORT.md](./SUPPORT.md)
- vulnerabilidades: siga [SECURITY.md](./SECURITY.md) e nao abra issue publica
