<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[var(--brand-primary)]">Tenant Dashboard</p>
                <h2 class="mt-2 font-display text-3xl font-bold tracking-tight text-slate-950">
                    {{ __('Painel da Operação') }}
                </h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    Acompanhe vendas do dia, alertas de estoque e ordens de serviço em uma visão rápida da sua revenda.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <span class="brand-pill border-emerald-200 bg-emerald-50 text-emerald-700">Sistema online</span>
                <span class="brand-pill border-amber-200 bg-amber-50 text-amber-700">White label ativo</span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article class="brand-metric-card border-slate-200">
                <div class="absolute inset-x-0 top-0 h-1 bg-[var(--brand-primary)]"></div>
                <p class="text-sm font-medium text-slate-500">Vendas do Dia</p>
                <div class="mt-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="font-display text-4xl font-bold tracking-tight text-slate-950">R$ 18,4 mil</p>
                        <p class="mt-2 text-sm text-emerald-600">+12% vs. ontem</p>
                    </div>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[rgba(var(--brand-primary-rgb),0.12)] text-[var(--brand-primary)]">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M3 17h14v-2H3v2zM5 13h2V7H5v6zm4 0h2V3H9v10zm4 0h2V9h-2v4z"/></svg>
                    </span>
                </div>
            </article>

            <article class="brand-metric-card border-amber-100 bg-gradient-to-br from-amber-50 to-white">
                <div class="absolute inset-x-0 top-0 h-1 bg-[var(--brand-secondary)]"></div>
                <p class="text-sm font-medium text-slate-500">Estoque Baixo</p>
                <div class="mt-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="font-display text-4xl font-bold tracking-tight text-slate-950">8 itens</p>
                        <p class="mt-2 text-sm text-amber-700">2 modelos críticos</p>
                    </div>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M8.257 3.099c.765-1.36 2.72-1.36 3.485 0l6.518 11.591c.75 1.334-.213 2.99-1.742 2.99H3.48c-1.53 0-2.492-1.656-1.742-2.99L8.257 3.1zM11 14V8H9v6h2zm0 2H9v2h2v-2z"/></svg>
                    </span>
                </div>
            </article>

            <article class="brand-metric-card border-emerald-100 bg-gradient-to-br from-emerald-50 to-white">
                <div class="absolute inset-x-0 top-0 h-1 bg-[var(--brand-success)]"></div>
                <p class="text-sm font-medium text-slate-500">OS Abertas</p>
                <div class="mt-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="font-display text-4xl font-bold tracking-tight text-slate-950">12</p>
                        <p class="mt-2 text-sm text-emerald-700">7 dentro do SLA</p>
                    </div>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M4 4h12v2H4V4zm0 4h12v8H4V8zm2 2v4h8v-4H6z"/></svg>
                    </span>
                </div>
            </article>

            <article class="brand-metric-card border-slate-200 bg-slate-950 text-white">
                <div class="absolute inset-x-0 top-0 h-1 bg-[var(--brand-secondary)]"></div>
                <p class="text-sm font-medium text-slate-300">Coleta de Sucata</p>
                <div class="mt-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="font-display text-4xl font-bold tracking-tight">1,28 t</p>
                        <p class="mt-2 text-sm text-slate-300">Meta semanal em 83%</p>
                    </div>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-[var(--brand-secondary)]">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M6 2h8l1 4h2a1 1 0 011 1v9a2 2 0 01-2 2H4a2 2 0 01-2-2V7a1 1 0 011-1h2l1-4zm1.2 4h5.6l-.5-2H7.7l-.5 2zM6 9v6h8V9H6z"/></svg>
                    </span>
                </div>
            </article>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
            <section class="brand-card">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Visão rápida</p>
                        <h3 class="mt-2 font-display text-2xl font-semibold text-slate-950">Prioridades operacionais</h3>
                    </div>
                    <span class="brand-pill border-slate-200 bg-slate-50 text-slate-600">Atualizado em tempo real</span>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-medium text-slate-500">Recebimentos pendentes</p>
                        <p class="mt-3 font-display text-3xl font-bold text-slate-950">R$ 6,2 mil</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-medium text-slate-500">Entregas em rota</p>
                        <p class="mt-3 font-display text-3xl font-bold text-slate-950">14</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-medium text-slate-500">Garantias procedentes</p>
                        <p class="mt-3 font-display text-3xl font-bold text-slate-950">4</p>
                    </div>
                </div>
            </section>

            <section class="brand-card bg-[linear-gradient(135deg,rgba(var(--brand-primary-rgb),0.98)_0%,var(--brand-primary-dark)_100%)] text-white">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-200">Meta Comercial</p>
                <p class="mt-4 font-display text-4xl font-bold">R$ 92 mil</p>
                <p class="mt-3 text-sm leading-6 text-slate-200">Faltam R$ 18 mil para fechar a meta mensal. Priorize orçamento convertido e estoque crítico.</p>
                <div class="mt-6 h-3 overflow-hidden rounded-full bg-white/10">
                    <div class="h-full w-[80%] rounded-full bg-[var(--brand-secondary)]"></div>
                </div>
                <p class="mt-3 text-xs uppercase tracking-[0.18em] text-slate-300">80% atingido</p>
            </section>
        </div>

        <div class="space-y-6">
            @if(auth()->user()->isSuperAdmin())
                <div class="brand-card">
                    <h3 class="mb-4 font-display text-xl font-semibold text-slate-950">Contexto de Empresa</h3>
                    <livewire:filial-selector lazy />
                </div>
            @endif

            @if(auth()->user()->hasRole(['dono', 'gestor', 'super_admin']))
                <div class="brand-card">
                    <h3 class="mb-4 font-display text-xl font-semibold text-slate-950">Gerenciamento de Usuários</h3>
                    <livewire:user-manager lazy />
                </div>
            @else
                <div class="brand-shell">
                    <x-welcome />
                </div>
            @endif

            @can('acesso-estoque')
                <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
                    <livewire:estoque-dashboard lazy />
                    <livewire:estoque-adjustment-form lazy />
                </div>

                <div class="grid gap-6 xl:grid-cols-2">
                    <livewire:xml-import-form lazy />
                    <livewire:conta-sucata-dashboard lazy />
                </div>
            @endcan

            @can('acesso-vendas')
                <div class="grid gap-6 xl:grid-cols-[1.35fr_0.95fr]">
                    <livewire:vale-form lazy />
                    <div class="space-y-6">
                        <livewire:vale-conversion-actions lazy />
                        @can('acesso-tecnico')
                            <livewire:ordem-servico-form lazy />
                        @endcan
                    </div>
                </div>

                <livewire:vale-list lazy />
            @endcan

            @can('acesso-logistica')
                <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                    <livewire:route-planner lazy />
                    <livewire:logistics-dashboard lazy />
                </div>

                <livewire:delivery-route-screen lazy />
            @endcan

            @can('acesso-tecnico')
                <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                    <livewire:garantia-board lazy />
                    <livewire:garantia-form lazy />
                </div>

                <livewire:garantia-laudo-form lazy />
            @endcan

            @can('acesso-financeiro')
                <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                    <livewire:finance-dashboard lazy />
                    <livewire:cash-flow-panel lazy />
                </div>

                <livewire:margin-analysis-grid lazy />

                <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                    <livewire:fiscal-contingency-dashboard lazy />
                    <livewire:cnab-upload-panel lazy />
                </div>
            @endcan

            @can('view-integration-operations')
                <livewire:integration-backbone-dashboard lazy />
            @endcan
        </div>
    </div>
</x-app-layout>
