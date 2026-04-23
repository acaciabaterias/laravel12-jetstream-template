<div>
    @php
        $canManageUsers = auth()->user()->hasRole(['dono', 'gestor']);
    @endphp

    @if($canManageUsers)
        <div class="mb-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ $editingUserId ? 'Editar Usuario' : 'Adicionar Usuario' }}
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">Gerencie perfis operacionais dentro do contexto atual.</p>
                </div>
                @if($editingUserId)
                    <button
                        type="button"
                        wire:click="cancelEditing"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    >
                        Cancelar edicao
                    </button>
                @endif
            </div>

            <form wire:submit="{{ $editingUserId ? 'updateUser' : 'createUser' }}" class="space-y-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Nome</label>
                        <input type="text" wire:model.live="name" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Email</label>
                        <input type="email" wire:model.live="email" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">
                            {{ $editingUserId ? 'Nova senha (opcional)' : 'Senha' }}
                        </label>
                        <input type="password" wire:model.live="password" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Papel</label>
                        <select wire:model.live="papel" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecione...</option>
                            @foreach($availableRoles as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('papel') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700">Status</label>
                        <div class="mt-2 flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <input id="ativo" type="checkbox" wire:model.live="ativo" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="ativo" class="text-sm text-slate-600">Usuario ativo e liberado para autenticar</label>
                        </div>
                        @error('ativo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">
                        {{ $editingUserId ? 'Salvar alteracoes' : 'Criar usuario' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <h3 class="text-lg font-semibold text-slate-900">Usuarios do contexto atual</h3>
            <p class="mt-1 text-sm text-slate-500">Papéis operacionais vinculados ao tenant/filial em uso.</p>
        </div>

        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Papel</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</th>
                    @if($canManageUsers)
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Acoes</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($users as $user)
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-900">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ ucfirst($user->papel) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $user->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        @if($canManageUsers)
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        wire:click="editUser({{ $user->id }})"
                                        class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="toggleActive({{ $user->id }})"
                                        class="rounded-xl px-3 py-2 text-sm font-medium transition {{ $user->ativo ? 'border border-rose-200 text-rose-700 hover:bg-rose-50' : 'border border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}"
                                    >
                                        {{ $user->ativo ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canManageUsers ? 5 : 4 }}" class="px-6 py-10 text-center text-sm text-slate-500">
                            Nenhum usuario encontrado para o contexto atual.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
