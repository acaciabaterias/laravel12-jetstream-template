<div class="space-y-8">
    <div class="flex items-center gap-4">
        <a
            href="{{ route('admin.clientes.index') }}"
            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900"
        >
            <span class="sr-only">Voltar</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.56l3.22 3.22a.75.75 0 1 1-1.06 1.06l-4.5-4.5a.75.75 0 0 1 0-1.06l4.5-4.5a.75.75 0 0 1 1.06 1.06L5.56 9.25h10.69A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
            </svg>
        </a>

        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Cadastro central</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                {{ $tenant ? 'Editar tenant' : 'Novo tenant' }}
            </h1>
        </div>
    </div>

    <form wire:submit="save" class="grid gap-8 xl:grid-cols-[minmax(0,1.3fr),22rem]">
        <section class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="cnpj" class="mb-2 block text-sm font-semibold text-slate-700">CNPJ</label>
                    <input id="cnpj" type="text" wire:model="cnpj" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    @error('cnpj') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="subdominio" class="mb-2 block text-sm font-semibold text-slate-700">Subdominio</label>
                    <input id="subdominio" type="text" wire:model.live="subdominio" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    @error('subdominio') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="razao-social" class="mb-2 block text-sm font-semibold text-slate-700">Razao social</label>
                    <input id="razao-social" type="text" wire:model.blur="razaoSocial" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    @error('razaoSocial') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="nome-fantasia" class="mb-2 block text-sm font-semibold text-slate-700">Nome fantasia</label>
                    <input id="nome-fantasia" type="text" wire:model="nomeFantasia" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    @error('nomeFantasia') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="telefone" class="mb-2 block text-sm font-semibold text-slate-700">Telefone</label>
                    <input id="telefone" type="text" wire:model="telefone" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    @error('telefone') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="email-contato" class="mb-2 block text-sm font-semibold text-slate-700">Email de contato</label>
                    <input id="email-contato" type="email" wire:model="emailContato" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    @error('emailContato') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Plano e status</h2>
                <div class="mt-5 space-y-5">
                    <div>
                        <label for="plano" class="mb-2 block text-sm font-semibold text-slate-700">Plano</label>
                        <select id="plano" wire:model="plano" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                            @forelse ($planOptions as $planOption)
                                <option value="{{ $planOption->slug }}">{{ $planOption->nome }}</option>
                            @empty
                                <option value="essential">Essential</option>
                                <option value="pro">Pro</option>
                                <option value="enterprise">Enterprise</option>
                            @endforelse
                        </select>
                        @error('plano') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                        <select id="status" wire:model="status" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-slate-900 p-6 text-white shadow-sm">
                <h2 class="text-lg font-semibold">Provisionamento</h2>
                <p class="mt-3 text-sm leading-6 text-slate-300">
                    O cadastro central fica pronto agora. Quando a infraestrutura estiver disponivel, o tenant podera ser provisionado no banco dedicado com o pipeline ja preparado.
                </p>
            </section>

            <div class="flex gap-3">
                <a href="{{ route('admin.clientes.index') }}" class="inline-flex flex-1 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition hover:bg-slate-800">
                    {{ $tenant ? 'Salvar alteracoes' : 'Criar tenant' }}
                </button>
            </div>
        </aside>
    </form>
</div>
