<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Gestão do Módulo 001</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Filiais</h2>
            </div>

            <a
                href="{{ route('admin.filiais.create') }}"
                class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500"
            >
                Nova Filial
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Filial</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">CNPJ</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Usuários</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Criada em</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($filiais as $filial)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900">{{ $filial->nome }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $filial->cnpj ?: 'Não informado' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $filial->users_count }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $filial->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <a
                                            href="{{ route('admin.filiais.edit', $filial) }}"
                                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                        >
                                            Editar
                                        </a>

                                        <form action="{{ route('admin.filiais.destroy', $filial) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50"
                                            >
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                                    Nenhuma filial cadastrada até o momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                {{ $filiais->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
