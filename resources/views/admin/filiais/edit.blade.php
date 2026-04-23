<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Gestão do Módulo 001</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Editar Filial</h2>
            </div>

            <a
                href="{{ route('admin.filiais.index') }}"
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                Voltar
            </a>
        </div>
    </x-slot>

    <div class="grid gap-8 xl:grid-cols-[minmax(0,2fr),minmax(20rem,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <form action="{{ route('admin.filiais.update', $filial) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="nome" class="block text-sm font-semibold text-slate-700">Nome da filial</label>
                    <input
                        id="nome"
                        name="nome"
                        type="text"
                        value="{{ old('nome', $filial->nome) }}"
                        class="mt-2 w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('nome')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cnpj" class="block text-sm font-semibold text-slate-700">CNPJ</label>
                    <input
                        id="cnpj"
                        name="cnpj"
                        type="text"
                        value="{{ old('cnpj', $filial->cnpj) }}"
                        class="mt-2 w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('cnpj')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a
                        href="{{ route('admin.filiais.index') }}"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancelar
                    </a>
                    <button
                        type="submit"
                        class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500"
                    >
                        Atualizar filial
                    </button>
                </div>
            </form>
        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Resumo</h3>
            <dl class="mt-4 space-y-4 text-sm text-slate-600">
                <div>
                    <dt class="font-semibold text-slate-900">ID</dt>
                    <dd class="mt-1">{{ $filial->id }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-900">Usuários vinculados</dt>
                    <dd class="mt-1">{{ $filial->users()->count() }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-900">Criada em</dt>
                    <dd class="mt-1">{{ $filial->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </aside>
    </div>
</x-admin-layout>
