<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-[var(--brand-primary)]">{{ __('Central Control') }}</p>
                <h2 class="mt-2 font-display text-3xl font-bold tracking-tight text-slate-900">{{ __('Platform Dashboard') }}</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">{{ __('Monitor monthly billing, tenant base and subscription health in an executive panel.') }}</p>
            </div>
            <a
                href="{{ route('admin.filiais.index') }}"
                class="inline-flex items-center rounded-2xl bg-[var(--brand-primary)] px-4 py-2.5 text-sm font-semibold text-white shadow-brand transition hover:opacity-95"
            >
                {{ __('Manage Tenants') }}
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-5">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Tenants Ativos</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight text-slate-900">{{ $stats['filiais'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Empresas operando no banco central e prontas para escalar.</p>
            </div>

            <div class="overflow-hidden rounded-3xl border border-[rgba(var(--brand-primary-rgb),0.15)] bg-[linear-gradient(135deg,rgba(var(--brand-primary-rgb),0.08)_0%,#ffffff_100%)] p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Usuários ERP</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight text-slate-900">{{ $stats['usuarios'] }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ $stats['usuarios_ativos'] }} usuários marcados como ativos.</p>
            </div>

            <div class="overflow-hidden rounded-3xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Clientes SaaS</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight text-slate-900">{{ $stats['clientes_ativos'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Tenants com assinatura ativa no catálogo central.</p>
            </div>

            <div class="overflow-hidden rounded-3xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">White Labels</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight text-slate-900">{{ $stats['white_labels'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Configurações visuais já registradas.</p>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-900 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-300">Faturamento Mensal</p>
                <p class="mt-4 text-2xl font-semibold tracking-tight text-white">{{ $stats['monthly_billing'] }}</p>
                <p class="mt-2 text-sm text-slate-400">Receita recorrente estimada com base na carteira ativa.</p>
            </div>
        </div>

        <div class="grid gap-8 xl:grid-cols-[minmax(0,2fr),minmax(22rem,1fr)]">
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                    <div>
                        <h3 class="font-display text-lg font-semibold text-slate-900">Tenants recentes</h3>
                        <p class="mt-1 text-sm text-slate-500">Resumo das contas recém-provisionadas na plataforma.</p>
                    </div>
                    <a href="{{ route('admin.filiais.index') }}" class="text-sm font-semibold text-[var(--brand-primary)] transition hover:opacity-80">
                        Ver listagem completa
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Tenant</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">CNPJ</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Usuários</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Assinatura</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($recentFiliais as $filial)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-900">{{ $filial->nome }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $filial->cnpj ?: 'Não informado' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $filial->users_count }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">
                                            Ativa
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">
                                        Nenhum tenant cadastrado ainda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div>
                    <h3 class="font-display text-lg font-semibold text-slate-900">Resumo financeiro e assinaturas</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Visão executiva para faturamento mensal, retenção e status comercial da base.</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900">MRR projetado</p>
                    <p class="mt-3 font-display text-3xl font-bold text-slate-950">{{ $stats['projected_mrr'] }}</p>
                    <p class="mt-1 text-sm text-slate-500">Com upgrade estimado de 12% na carteira ativa.</p>
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Assinaturas ativas</p>
                                <p class="mt-1 text-sm text-slate-600">Base adimplente e em operação.</p>
                            </div>
                            <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">Saudável</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Assinaturas em risco</p>
                                <p class="mt-1 text-sm text-slate-600">Clientes próximos do vencimento ou sem cobrança confirmada.</p>
                            </div>
                            <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-amber-700">Atenção</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="grid gap-8">
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                @livewire('admin.audit-log-widget')
            </section>
        </div>
    </div>
</x-admin-layout>
