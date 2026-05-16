<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Gerenciamento de Fabricantes</h2>
        <div class="flex space-x-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar fabricantes..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <x-button wire:click="create">Novo Fabricante</x-button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($fabricantes as $fabricante)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $fabricante->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $fabricante->nome }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $fabricante->codigo ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($fabricante->trashed())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="edit({{ $fabricante->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                            <button wire:click="toggleStatus({{ $fabricante->id }})" class="text-{{ $fabricante->trashed() ? 'green' : 'red' }}-600 hover:text-{{ $fabricante->trashed() ? 'green' : 'red' }}-900">
                                {{ $fabricante->trashed() ? 'Reativar' : 'Inativar' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhum fabricante encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($fabricantes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $fabricantes->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form -->
    <x-dialog-modal wire:model="showModal">
        <x-slot name="title">
            {{ $isEditMode ? 'Editar' : 'Novo' }} Fabricante
        </x-slot>

        <x-slot name="content">
            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-label for="nome" value="Nome do Fabricante *" />
                <x-input id="nome" type="text" class="mt-1 block w-full" wire:model="nome" required />
                <x-input-error for="nome" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-label for="codigo" value="Código Interno (Opcional)" />
                <x-input id="codigo" type="text" class="mt-1 block w-full" wire:model="codigo" />
                <x-input-error for="codigo" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showModal', false)" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>

            <x-button class="ml-2" wire:click="store" wire:loading.attr="disabled">
                Salvar
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
