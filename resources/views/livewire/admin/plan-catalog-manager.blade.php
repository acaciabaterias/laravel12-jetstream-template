<div class="space-y-8">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Billing Control Plane</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Catalogo de planos</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Cadastre e mantenha as ofertas comerciais centrais da plataforma.</p>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1.1fr),minmax(0,0.9fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Novo plano</h2>

            <form wire:submit="save" class="mt-6 grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Nome</label>
                    <input type="text" wire:model="nome" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    @error('nome') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Slug</label>
                    <input type="text" wire:model="slug" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    @error('slug') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Periodicidade</label>
                    <select wire:model="periodicidade" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        <option value="mensal">Mensal</option>
                        <option value="trimestral">Trimestral</option>
                        <option value="anual">Anual</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Preco mensal</label>
                    <input type="number" step="0.01" wire:model="precoMensal" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    @error('preco_mensal') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Preco anual</label>
                    <input type="number" step="0.01" wire:model="precoAnual" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Max. usuarios</label>
                    <input type="number" wire:model="maxUsuarios" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Max. itens estoque</label>
                    <input type="number" wire:model="maxEstoqueItens" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700">
                    <input type="checkbox" wire:model="hasWhiteLabel" class="rounded border-slate-300 text-slate-900 focus:ring-slate-400">
                    White label
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700">
                    <input type="checkbox" wire:model="hasSupportPriority" class="rounded border-slate-300 text-slate-900 focus:ring-slate-400">
                    Suporte prioritario
                </label>
                <label class="md:col-span-2 flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700">
                    <input type="checkbox" wire:model="ativo" class="rounded border-slate-300 text-slate-900 focus:ring-slate-400">
                    Plano ativo
                </label>
                <div class="md:col-span-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition hover:bg-slate-800">
                        Criar plano
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Planos atuais</h2>
            <div class="mt-5 space-y-4">
                @forelse ($plans as $plan)
                    <article class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $plan->nome }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $plan->slug }} · {{ strtoupper($plan->periodicidade) }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $plan->ativo ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $plan->ativo ? 'ATIVO' : 'INATIVO' }}
                            </span>
                        </div>
                        <div class="mt-4 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                            <p>Mensal: R$ {{ number_format((float) $plan->preco_mensal, 2, ',', '.') }}</p>
                            <p>Usuarios: {{ $plan->max_usuarios }}</p>
                            <p>Estoque: {{ $plan->max_estoque_itens }}</p>
                            <p>{{ $plan->has_white_label ? 'Com white label' : 'Sem white label' }}</p>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-slate-500">Nenhum plano cadastrado.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
