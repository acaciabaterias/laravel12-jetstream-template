# Implementation Plan: Módulo de Usuários e Perfis (RBAC)

**Branch**: `002-users-permissions-rbac`
**Input**: Feature specification from `/specs/002-users-permissions-rbac/spec.md`

## Technical Context
**Primary Dependencies**: Laravel 12, Livewire 4, Tailwind CSS 4
**Storage**: PostgreSQL 15+

## Project Structure
- **Laravel Framework**: Utilização nativa do subsistema de Autorização (Policy/Gates). Jetstream ou base Fortify para login.
- **Tabelas**: Modificação da tabela `users`, criação da tabela `roles` (ou ENUM), criação da tabela de junção `filial_user`.

## Estrutura de Banco de Dados

### Tabela `users` (estendida)

```sql
ALTER TABLE users ADD COLUMN filial_id BIGINT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN papel ENUM('super_admin', 'dono', 'gestor', 'vendedor', 'tecnico', 'estoquista') NOT NULL DEFAULT 'vendedor';
ALTER TABLE users ADD COLUMN ativo BOOLEAN DEFAULT TRUE;

-- Índices
CREATE INDEX idx_users_filial_id ON users(filial_id);
CREATE INDEX idx_users_papel ON users(papel);

-- Foreign key (opcional, pois super_admin tem filial_id NULL)
ALTER TABLE users ADD CONSTRAINT fk_users_filial_id FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE SET NULL;
```

## Middleware FilialIsolation

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FilialIsolation
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        // Super admin tem acesso irrestrito
        if ($user && $user->papel === 'super_admin') {
            return $next($request);
        }
        
        // Usuário comum: filial_id obrigatório
        if (!$user || !$user->filial_id) {
            abort(403, 'Usuário não associado a nenhuma filial');
        }
        
        // Força o contexto da filial na sessão
        session(['filial_id' => $user->filial_id]);
        
        // Verifica acesso a recursos de outras filiais
        $rotaFilialId = $request->route('filial_id') ?? $request->input('filial_id');
        if ($rotaFilialId && $rotaFilialId != $user->filial_id) {
            abort(403, 'Acesso negado: você não pertence a esta filial');
        }
        
        return $next($request);
    }
}
```

### Registro em bootstrap/app.php:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'filial.isolation' => \App\Http\Middleware\FilialIsolation::class,
    ]);
})
```

## Seeder do Super Admin

```php
// database/seeders/SuperAdminSeeder.php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'admin@bateriaexpert.com')],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', '12345678')),
                'papel' => 'super_admin',
                'filial_id' => null,
                'ativo' => true,
            ]
        );
    }
}
```

