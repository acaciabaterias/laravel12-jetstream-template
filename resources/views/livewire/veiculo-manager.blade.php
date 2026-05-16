<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 border-b pb-4">
        <h2 class="text-xl font-semibold text-gray-800">Gerenciamento de Veículos & Aplicações</h2>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 w-full md:w-auto">
            <select wire:model.live="fabricanteFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">Todos os Fabricantes</option>
                @foreach($fabricantes as $fab)
                    <option value="{{ $fab->id }}">{{ $fab->nome }}</option>
                @endforeach
            </select>
            <input type="number" wire:model.live.debounce.300ms="anoFilter" placeholder="Ano" class="w-28 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar veículo..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <x-button wire:click="create">Novo Veículo</x-button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montadora / Modelo</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motorização</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aplicações</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($veiculos as $veiculo)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ $veiculo->fabricante->nome ?? 'N/A' }} / <span class="text-indigo-600">{{ $veiculo->modelo }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $veiculo->ano_inicio ?? '...' }} - {{ $veiculo->ano_fim ?? 'Atual' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $veiculo->motorizacao ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $veiculo->baterias()->count() }} baterias
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($veiculo->trashed())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="$dispatch('openCloner', { destinoId: {{ $veiculo->id }} })" class="text-blue-600 hover:text-blue-900 mr-3">Clonar</button>
                            <button wire:click="edit({{ $veiculo->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar/Aplicações</button>
                            <button wire:click="toggleStatus({{ $veiculo->id }})" class="text-{{ $veiculo->trashed() ? 'green' : 'red' }}-600 hover:text-{{ $veiculo->trashed() ? 'green' : 'red' }}-900">
                                {{ $veiculo->trashed() ? 'Reativar' : 'Inativar' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhum veículo encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($veiculos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $veiculos->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form (Veículos e Aplicações) -->
    <x-dialog-modal wire:model="showModal" maxWidth="4xl">
        <x-slot name="title">
            {{ $isEditMode ? 'Editar' : 'Novo' }} Veículo: {{ $modelo }}
        </x-slot>

        <x-slot name="content">
            <!-- Tabs -->
            <div class="border-b border-gray-200 mt-2 mb-4">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button wire:click="setTab('basico')" class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm {{ $currentTab === 'basico' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Dados Básicos
                    </button>
                    <button wire:click="setTab('aplicacoes')" class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm {{ $currentTab === 'aplicacoes' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Aplicações (Baterias)
                        @if(count($aplicacoes) > 0)
                            <span class="bg-indigo-100 text-indigo-600 py-0.5 px-2.5 rounded-full text-xs ml-2">{{ count($aplicacoes) }}</span>
                        @endif
                    </button>
                </nav>
            </div>

            <div class="mt-4">
                @if($currentTab === 'basico')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label for="fabricante_id" value="Fabricante (Montadora) *" />
                            <select id="fabricante_id" wire:model="fabricante_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Selecione...</option>
                                @foreach($fabricantes as $fab)
                                    <option value="{{ $fab->id }}">{{ $fab->nome }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="fabricante_id" class="mt-1" />

                            <div class="mt-4">
                                <x-label for="modelo" value="Modelo *" />
                                <x-input id="modelo" type="text" class="mt-1 block w-full" wire:model="modelo" required placeholder="Ex: Onix Hatch LTZ" />
                                <x-input-error for="modelo" class="mt-1" />
                            </div>

                            <div class="mt-4">
                                <x-label for="motorizacao" value="Motorização" />
                                <x-input id="motorizacao" type="text" class="mt-1 block w-full" wire:model="motorizacao" placeholder="Ex: 1.0 12V Flex" />
                                <x-input-error for="motorizacao" class="mt-1" />
                            </div>
                        </div>

                        <div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <x-label for="ano_inicio" value="Ano Início" />
                                    <x-input id="ano_inicio" type="number" class="mt-1 block w-full" wire:model="ano_inicio" placeholder="Ex: 2018" />
                                    <x-input-error for="ano_inicio" class="mt-1" />
                                </div>
                                <div>
                                    <x-label for="ano_fim" value="Ano Fim" />
                                    <x-input id="ano_fim" type="number" class="mt-1 block w-full" wire:model="ano_fim" placeholder="Ex: 2023" />
                                    <x-input-error for="ano_fim" class="mt-1" />
                                    <span class="text-xs text-gray-500">Deixe vazio se for o modelo atual</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-label for="atributos_dinamicos" value="Atributos Dinâmicos (JSON Livre)" />
                                <textarea id="atributos_dinamicos" wire:model="atributos_dinamicos" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-mono text-sm" placeholder='{"direcao": "eletrica", "opcionais": ["start_stop"]}'></textarea>
                                <x-input-error for="atributos_dinamicos" class="mt-1" />
                            </div>
                        </div>
                    </div>
                @endif

                @if($currentTab === 'aplicacoes')
                    @if($isEditMode && $veiculoId)
                        <livewire:application-manager :vehicle-id="$veiculoId" :key="'application-manager-'.$veiculoId" />
                    @else
                        <p class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Salve o veículo primeiro para gerenciar aplicações na aba dedicada.
                        </p>
                    @endif
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showModal', false)" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>

            <x-button class="ml-2" wire:click="store" wire:loading.attr="disabled">
                Salvar Veículo & Aplicações
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <livewire:aplicacao-cloner />
</div>
