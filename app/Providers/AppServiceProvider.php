<?php

namespace App\Providers;

use App\Models\BoletoOrquestrado;
use App\Models\Cliente;
use App\Models\CnabRemessa;
use App\Models\CnabRetornoUpload;
use App\Models\ContaBancaria;
use App\Models\Deposito;
use App\Models\EstoqueMovimentacao;
use App\Models\FilaContingencia;
use App\Models\NotaFiscalOrquestrada;
use App\Models\OrdemServico;
use App\Models\OrdemServicoGarantia;
use App\Models\PedidoVenda;
use App\Models\ReservaEstoque;
use App\Models\TransacaoFinanceira;
use App\Models\User;
use App\Models\Vale;
use App\Policies\BoletoOrquestradoPolicy;
use App\Policies\CnabRemessaPolicy;
use App\Policies\CnabRetornoUploadPolicy;
use App\Policies\ContaBancariaPolicy;
use App\Policies\DepositoPolicy;
use App\Policies\EstoqueMovimentacaoPolicy;
use App\Policies\FilaContingenciaPolicy;
use App\Policies\NotaFiscalOrquestradaPolicy;
use App\Policies\OrdemServicoGarantiaPolicy;
use App\Policies\OrdemServicoPolicy;
use App\Policies\PedidoVendaPolicy;
use App\Policies\ReservaEstoquePolicy;
use App\Policies\TenantPolicy;
use App\Policies\TransacaoFinanceiraPolicy;
use App\Policies\UserPolicy;
use App\Policies\ValePolicy;
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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Cliente::class, TenantPolicy::class);
        Gate::policy(Vale::class, ValePolicy::class);
        Gate::policy(PedidoVenda::class, PedidoVendaPolicy::class);
        Gate::policy(OrdemServico::class, OrdemServicoPolicy::class);
        Gate::policy(OrdemServicoGarantia::class, OrdemServicoGarantiaPolicy::class);
        Gate::policy(Deposito::class, DepositoPolicy::class);
        Gate::policy(EstoqueMovimentacao::class, EstoqueMovimentacaoPolicy::class);
        Gate::policy(ReservaEstoque::class, ReservaEstoquePolicy::class);
        Gate::policy(ContaBancaria::class, ContaBancariaPolicy::class);
        Gate::policy(TransacaoFinanceira::class, TransacaoFinanceiraPolicy::class);
        Gate::policy(NotaFiscalOrquestrada::class, NotaFiscalOrquestradaPolicy::class);
        Gate::policy(BoletoOrquestrado::class, BoletoOrquestradoPolicy::class);
        Gate::policy(CnabRemessa::class, CnabRemessaPolicy::class);
        Gate::policy(CnabRetornoUpload::class, CnabRetornoUploadPolicy::class);
        Gate::policy(FilaContingencia::class, FilaContingenciaPolicy::class);

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
            return $user->hasRole(['dono', 'gestor']);
        });

        Gate::define('view-platform-dashboard', function ($user) {
            return $user instanceof \App\Models\UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support', 'billing']);
        });

        Gate::define('manage-tenants', function ($user) {
            return $user instanceof \App\Models\UsuarioPlataforma
                && $user->ativo
                && $user->isSuperAdmin();
        });

        Gate::define('manage-platform-billing', function ($user) {
            return $user instanceof \App\Models\UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'billing']);
        });

        Gate::define('manage-platform-support', function ($user) {
            return $user instanceof \App\Models\UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support']);
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

        Gate::define('acesso-logistica', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'entregador']);
        });

        Gate::define('acesso-financeiro', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
        });

        Gate::define('acesso-tecnico', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'tecnico']);
        });

        Gate::define('gerenciar-vales', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'vendedor']);
        });

        Gate::define('gerenciar-pedidos-venda', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'vendedor']);
        });

        Gate::define('gerenciar-ordens-servico', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'tecnico']);
        });

        Gate::define('movimentar-estoque', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'estoquista']);
        });

        Gate::define('gerenciar-financeiro-avancado', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
        });

        Gate::define('emitir-documentos-fiscais', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'vendedor']);
        });

        Gate::define('processar-cnab', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
        });

        Gate::define('gerenciar-contingencia', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
        });
    }
}
