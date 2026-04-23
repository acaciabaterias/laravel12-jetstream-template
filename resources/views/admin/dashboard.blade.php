<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Controle Central</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Dashboard da Plataforma</h2>
            </div>
            <a
                href="{{ route('admin.filiais.index') }}"
                class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-slate-800"
            >
                Gerenciar Filiais
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-5">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Filiais Ativas</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight text-slate-900">{{ $stats['filiais'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Bases operacionais cadastradas no tenant central.</p>
            </div>

            <div class="overflow-hidden rounded-3xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-6 shadow-sm">
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
                <p class="text-sm font-medium text-slate-300">Próximo Passo</p>
                <p class="mt-4 text-2xl font-semibold tracking-tight text-white">Provisionar e organizar tenant.</p>
                <p class="mt-2 text-sm text-slate-400">Use a gestão de filiais para preparar o contexto operacional do módulo 001.</p>
            </div>
        </div>

        <div class="grid gap-8 xl:grid-cols-[minmax(0,2fr),minmax(22rem,1fr)]">
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Filiais recentes</h3>
                        <p class="mt-1 text-sm text-slate-500">Resumo rápido das bases já cadastradas.</p>
                    </div>
                    <a href="{{ route('admin.filiais.index') }}" class="text-sm font-semibold text-indigo-600 transition hover:text-indigo-500">
                        Ver listagem completa
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Filial</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">CNPJ</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Usuários</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Cadastro</th>
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
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $filial->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">
                                        Nenhuma filial cadastrada ainda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Camada tenant-aware</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    O dashboard central já convive com o <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">TenantConnectionMiddleware</code>,
                    mas em rotas administrativas centrais o bypass é obrigatório para não resolver o subdomínio <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">admin</code> como tenant.
                </p>

                <div class="mt-6 space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-semibold text-slate-900">FilialSelector</p>
                        <p class="mt-1 text-sm text-slate-600">O componente Livewire já existe para o dashboard autenticado da aplicação principal.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-semibold text-slate-900">White label</p>
                        <p class="mt-1 text-sm text-slate-600">As configurações visuais ficam disponíveis para futuras telas administrativas e tenant-specific branding.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-admin-layout>
