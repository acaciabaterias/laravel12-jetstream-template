# FAQ

## 1. O que e o ERP BateriaExpert?

E um ERP especializado no segmento de baterias, com foco em vendas, estoque, logistica reversa, garantias, financeiro, fiscal e integracoes por microservicos.

## 2. Qual stack principal do ERP Core?

- Laravel 12
- PHP 8.3+
- Livewire
- PostgreSQL
- Redis

## 3. Como funciona a multi-tenancy?

O projeto usa `database-per-client`. Cada tenant opera em seu proprio banco, enquanto o banco central guarda catalogo, assinaturas e metadados de plataforma.

## 4. Ainda existe `filial_id` como isolamento?

Nao. A arquitetura atual removeu o isolamento logico antigo e padronizou o modelo tenant-aware por banco.

## 5. Onde ficam os artefatos funcionais dos modulos?

Em `specs/`, com `spec.md`, `plan.md` e `tasks.md` por modulo e por microservico.

## 6. Como rodo os testes?

Use:

```bash
php artisan test --compact
```

## 7. Como subo o ambiente com containers?

Use:

```bash
cp .env.example .env
docker compose up --build -d
./healthcheck.sh
```

## 8. Onde vejo a documentacao de deploy?

Consulte:

- `README.md`
- `DEPLOY_PRODUCAO.md`
- `DEPLOY_PROXMOX.md`
- `DEPLOY_SUPABASE.md`

## 9. Como reporto vulnerabilidades?

Use o fluxo privado descrito em `SECURITY.md`. Nao abra issue publica para falhas de seguranca.

## 10. Como contribuir com codigo?

Comece por `CONTRIBUTING.md`, depois valide testes, arquitetura e impacto operacional antes de abrir PR.

## 11. Os microservicos ja estao no monorepo?

Sim. Eles vivem em `microservicos/` e possuem scaffold, `.env.example`, `Dockerfile` e stack declarada no `docker-compose.yml` da raiz.

## 12. O projeto esta pronto para producao?

Ele esta forte em arquitetura, codigo, testes e documentacao. O passo decisivo e a validacao do primeiro boot integrado e a homologacao operacional do stack Docker completo.
