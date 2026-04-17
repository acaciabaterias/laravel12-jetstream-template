<div>
    @if(auth()->user()->hasRole(['dono', 'gestor', 'super_admin']))
        <div class="mb-8 p-4 bg-white shadow rounded">
            <h3 class="text-lg font-medium mb-4">Adicionar Usuário</h3>
            <form wire:submit="createUser">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label>Nome</label>
                        <input type="text" wire:model="name" class="block w-full rounded border-gray-300">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label>Email</label>
                        <input type="email" wire:model="email" class="block w-full rounded border-gray-300">
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label>Senha</label>
                        <input type="password" wire:model="password" class="block w-full rounded border-gray-300">
                        @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label>Papel</label>
                        <select wire:model="papel" class="block w-full rounded border-gray-300">
                            <option value="">Selecione...</option>
                            <option value="dono">Dono</option>
                            <option value="gestor">Gestor</option>
                            <option value="vendedor">Vendedor</option>
                            <option value="tecnico">Técnico</option>
                            <option value="estoquista">Estoquista</option>
                        </select>
                        @error('papel') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Criar Usuário</button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white shadow rounded p-4">
        <h3 class="text-lg font-medium mb-4">Usuários do CNPJ Atual</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Papel</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($user->papel) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
