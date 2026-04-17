# Quickstart: Isolated Tenancy & Super Admin

## Setup Local
Nesta nova arquitetura, o banco de metadados central usa as variáveis de ambiente `DB_CENTRAL_*`, e a conexão dinâmica do tenant é montada em runtime.

### Para testar o painel administrativo:
1. Garanta que as migrações do banco central foram executadas:
   `php artisan migrate --path=database/migrations/central`
2. Gere o Super Admin inicial usando os seeders:
   `php artisan db:seed`
3. Inicie o servidor:
   `php artisan serve`
4. Acesse o dashboard administrativo utilizando um subdomínio (ou via configuração do `/etc/hosts`):
   `http://admin.localhost:8000/dashboard`
5. O painel está protegido pelo guard `platform`.

### Para testar a resolução do Tenant e os serviços do Cliente:
A arquitetura resolve automaticamente conexões para requisições recebidas em um subdomínio de ambiente de teste (`{cliente}.localhost:8000`).
* Durante o login no ERP, se o subdomínio não for "admin", o middleware `TenantConnectionMiddleware` verificará a assinatura na base central e trocará explicitamente para a base SQLite temporária de testes que mimetiza a base Supabase localmente.

### Criando Tenants via API (Mock):
Vá até `http://admin.localhost:8000/clientes/novo` e simule a criação de um Tenant. O processo validará os dados via formulário Livewire Volt, simulará o processo de criação de projeto (`tenant:create`) e atualizará a lista.
