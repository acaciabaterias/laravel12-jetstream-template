<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Gerenciamento de Baterias</h2>
        <div class="flex space-x-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por SKU ou Marca..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <x-button wire:click="create">Nova Bateria</x-button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marca/Tecnologia</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço (R$)</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($baterias as $bateria)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">{{ $bateria->sku }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $bateria->marca }} 
                            <span class="text-xs text-gray-500 block">{{ $bateria->tecnologia ?? '-' }} | {{ $bateria->amperagem ? $bateria->amperagem.'Ah' : '-' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($bateria->preco_venda, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($bateria->trashed())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="edit({{ $bateria->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                            <button wire:click="toggleStatus({{ $bateria->id }})" class="text-{{ $bateria->trashed() ? 'green' : 'red' }}-600 hover:text-{{ $bateria->trashed() ? 'green' : 'red' }}-900">
                                {{ $bateria->trashed() ? 'Reativar' : 'Inativar' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhuma bateria encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($baterias->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $baterias->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form -->
    <x-dialog-modal wire:model="showModal" maxWidth="4xl">
        <x-slot name="title">
            {{ $isEditMode ? 'Editar' : 'Nova' }} Bateria
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <!-- Informações Básicas -->
                <div class="border-r pr-4">
                    <h3 class="text-md font-medium text-gray-700 mb-3 border-b pb-2">Dados Básicos</h3>
                    
                    <div class="mb-3">
                        <x-label for="sku" value="SKU *" />
                        <x-input id="sku" type="text" class="mt-1 block w-full" wire:model="sku" required />
                        <x-input-error for="sku" class="mt-1" />
                    </div>

                    <div class="mb-3">
                        <x-label for="marca" value="Marca *" />
                        <x-input id="marca" type="text" class="mt-1 block w-full" wire:model="marca" required />
                        <x-input-error for="marca" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div>
                            <x-label for="tecnologia" value="Tecnologia" />
                            <select id="tecnologia" wire:model="tecnologia" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Selecione...</option>
                                <option value="Chumbo-Ácido">Chumbo-Ácido</option>
                                <option value="AGM">AGM</option>
                                <option value="EFB">EFB</option>
                                <option value="Gel">Gel</option>
                            </select>
                            <x-input-error for="tecnologia" class="mt-1" />
                        </div>
                        <div>
                            <x-label for="amperagem" value="Amperagem (Ah)" />
                            <x-input id="amperagem" type="number" class="mt-1 block w-full" wire:model="amperagem" />
                            <x-input-error for="amperagem" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div>
                            <x-label for="polo" value="Pólo" />
                            <select id="polo" wire:model="polo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Selecione...</option>
                                <option value="Direito">Direito (D)</option>
                                <option value="Esquerdo">Esquerdo (E)</option>
                            </select>
                            <x-input-error for="polo" class="mt-1" />
                        </div>
                        <div>
                            <x-label for="preco_venda" value="Preço Venda (R$) *" />
                            <x-input id="preco_venda" type="number" step="0.01" class="mt-1 block w-full" wire:model="preco_venda" required />
                            <x-input-error for="preco_venda" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Logística e Dinâmicos -->
                <div class="pl-2">
                    <h3 class="text-md font-medium text-gray-700 mb-3 border-b pb-2">Logística Reversa & Atributos</h3>
                    
                    <div class="mb-4 bg-gray-50 p-3 rounded text-sm">
                        <label class="flex items-center">
                            <x-checkbox wire:model="tem_logistica_reversa" />
                            <span class="ml-2 text-gray-700">Participa de Logística Reversa (Sucata base)</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div>
                            <x-label for="peso_sucata_kg" value="Peso Sucata (Kg)" />
                            <x-input id="peso_sucata_kg" type="number" step="0.01" class="mt-1 block w-full" wire:model="peso_sucata_kg" />
                            <x-input-error for="peso_sucata_kg" class="mt-1" />
                        </div>
                        <div>
                            <x-label for="valor_base_sucata_kg" value="Valor Base / Kg (R$)" />
                            <x-input id="valor_base_sucata_kg" type="number" step="0.01" class="mt-1 block w-full" wire:model="valor_base_sucata_kg" />
                            <x-input-error for="valor_base_sucata_kg" class="mt-1" />
                        </div>
                    </div>

                    <div class="mb-3 mt-4">
                        <x-label for="atributos_dinamicos" value="Atributos Dinâmicos (JSON Livre)" />
                        <textarea id="atributos_dinamicos" wire:model="atributos_dinamicos" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-mono text-sm" placeholder='{"cca": 450, "dimensoes": "20x17x19"}'></textarea>
                        <x-input-error for="atributos_dinamicos" class="mt-1" />
                        <span class="text-xs text-gray-500 mt-1 block">Apenas formato JSON válido. Vendedores apenas visualizam.</span>
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showModal', false)" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>

            <x-button class="ml-2" wire:click="store" wire:loading.attr="disabled">
                Salvar Bateria
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
