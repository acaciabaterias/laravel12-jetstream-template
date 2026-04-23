<div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Gestao de Tenants</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Clientes SaaS e provisionamento</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Cadastre tenants, acompanhe status operacionais e mantenha o catalogo central pronto para o provisionamento quando a infraestrutura estiver disponivel.
            </p>
        </div>

        <a
            href="{{ route('admin.clientes.create') }}"
            class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition hover:bg-slate-800"
        >
            Novo tenant
        </a>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-emerald-700">Ativos</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-emerald-900">{{ $stats['ativos'] }}</p>
        </div>
        <div class="rounded-3xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-sky-700">Trials</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-sky-900">{{ $stats['trial'] }}</p>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-amber-700">Expirados</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-amber-900">{{ $stats['expirados'] }}</p>
        </div>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Lista de tenants</h2>
                <p class="mt-1 text-sm text-slate-500">Pesquisa rapida por razao social, CNPJ ou subdominio.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <label class="sr-only" for="tenant-search">Buscar</label>
                <input
                    id="tenant-search"
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar tenant"
                    class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400 sm:w-72"
                >

                <select
                    wire:model.live="statusFilter"
                    class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400"
                >
                    <option value="all">Todos os status</option>
                    <option value="active">Ativo</option>
                    <option value="trial">Trial</option>
                    <option value="expired">Expirado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Tenant</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Contato</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Plano</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tenants as $tenant)
                        <tr wire:key="tenant-{{ $tenant->id }}" class="transition hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $tenant->razao_social }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $tenant->subdominio }}.erp.com</div>
                                <div class="mt-1 text-xs text-slate-400">{{ $tenant->cnpj }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <div>{{ $tenant->email_contato }}</div>
                                <div class="mt-1 text-xs text-slate-400">{{ $tenant->telefone ?: 'Sem telefone' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium capitalize text-slate-700">{{ $tenant->plano }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $tenant->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($tenant->status === 'trial' ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ strtoupper($tenant->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-3">
                                    <a
                                        href="{{ route('admin.clientes.edit', $tenant) }}"
                                        class="inline-flex items-center rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                    >
                                        Editar
                                    </a>

                                    <button
                                        type="button"
                                        wire:click="toggleStatus({{ $tenant->id }})"
                                        class="inline-flex items-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-800"
                                    >
                                        {{ $tenant->status === 'active' ? 'Expirar' : 'Ativar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                                Nenhum tenant encontrado para os filtros atuais.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-6 py-4">
            {{ $tenants->links() }}
        </div>
    </section>
</div>
