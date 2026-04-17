<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurações Globais de SEO
        seo()
            ->site('Promovaweb')
            ->title(
                default: 'Laravel 12 Jetstream Livewire Starter Kit',
                modify: fn (string $title) => $title.' | Promovaweb'
            )
            ->description(default: 'We are a development agency ...')
            ->twitterSite('@promovaweb');

        // RBAC Gates
        Gate::define('gerenciar-usuarios', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
        });

        Gate::define('gerenciar-assinatura', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole('dono');
        });

        Gate::define('acesso-vendas', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'vendedor']);
        });

        Gate::define('acesso-estoque', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'estoquista']);
        });

        Gate::define('acesso-tecnico', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'tecnico']);
        });
    }
}
