<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Gestão do Módulo 001</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Nova Filial</h2>
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
            <form action="{{ route('admin.filiais.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="nome" class="block text-sm font-semibold text-slate-700">Nome da filial</label>
                    <input
                        id="nome"
                        name="nome"
                        type="text"
                        value="{{ old('nome') }}"
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
                        value="{{ old('cnpj') }}"
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
                        Salvar filial
                    </button>
                </div>
            </form>
        </section>

        <aside class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Contexto operacional</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    A filial representa a base operacional usada pelos fluxos atuais de usuários e cadastros estruturais presentes no repositório.
                </p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">White label</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    @if ($brandingConfig)
                        Existe uma configuração visual cadastrada para reutilização nas telas do tenant.
                    @else
                        Nenhuma configuração visual foi cadastrada ainda.
                    @endif
                </p>
            </div>
        </aside>
    </div>
</x-admin-layout>
