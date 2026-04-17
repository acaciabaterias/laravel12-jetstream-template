<div class="p-6 bg-white overflow-hidden shadow-xl sm:rounded-lg max-w-4xl mx-auto">
    <h2 class="text-xl font-semibold text-gray-800 mb-6 border-b pb-4">Ajuste Manual de Estoque</h2>

    @if(session()->has('message'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
            <span class="font-medium">Sucesso!</span> {{ session('message') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            <span class="font-medium">Erro na Movimentação:</span> {{ session('error') }}
        </div>
    @endif

    <div class="space-y-6">
        <!-- 1. Bateria Selection -->
        <div>
            <x-label for="searchBateria" value="1. Selecione a Bateria" />
            <div class="relative mt-1">
                <x-input id="searchBateria" type="text" class="block w-full" wire:model.live.debounce.300ms="searchBateria" placeholder="Digite SKU ou Marca..." />
                
                @if(count($bateriasResults) > 0 && !$bateriaSelecionada)
                    <ul class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto sm:text-sm">
                        @foreach($bateriasResults as $bat)
                            <li wire:click="selectBateria({{ $bat->id }})" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50">
                                <span class="font-bold">{{ $bat->sku }}</span> - {{ $bat->marca }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <x-input-error for="bateria_id" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 2. Deposito -->
            <div>
                <x-label for="deposito_id" value="2. Depósito Alvo" />
                <select id="deposito_id" wire:model="deposito_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Selecione um depósito...</option>
                    @foreach($depositos as $dep)
                        <option value="{{ $dep->id }}">{{ $dep->nome }}</option>
                    @endforeach
                </select>
                <x-input-error for="deposito_id" class="mt-2" />
            </div>

            <!-- 3. Tipo e Quantidade -->
            <div class="flex space-x-4">
                <div class="flex-1">
                    <x-label for="tipo" value="3. Tipo de Ajuste" />
                    <select id="tipo" wire:model="tipo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="entrada">Entrada (+)</option>
                        <option value="saida">Saída (-)</option>
                    </select>
                    <x-input-error for="tipo" class="mt-2" />
                </div>
                <div class="flex-1">
                    <x-label for="quantidade" value="Quantidade" />
                    <x-input id="quantidade" type="number" min="1" class="mt-1 block w-full" wire:model="quantidade" />
                    <x-input-error for="quantidade" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- 4. Justificativa -->
        <div>
            <x-label for="justificativa" value="4. Justificativa (Obrigatório para Auditoria)" />
            <textarea id="justificativa" wire:model="justificativa" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Por que este ajuste está sendo feito?"></textarea>
            <x-input-error for="justificativa" class="mt-2" />
        </div>

        <div class="flex items-center justify-end pt-4 border-t">
            <x-button wire:click="save" wire:loading.attr="disabled" class="bg-indigo-600 hover:bg-indigo-700">
                Confirmar Ajuste
            </x-button>
        </div>
    </div>
</div>
