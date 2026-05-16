<div class="space-y-4">
    <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
        <h4 class="mb-2 text-sm font-medium text-gray-700">Aplicações do veículo {{ $veiculo->modelo }}</h4>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
            <div class="relative md:col-span-5">
                <x-input type="text" wire:model.live.debounce.300ms="searchBateria" class="w-full text-sm" placeholder="Buscar bateria por SKU ou marca..." />
                @if(count($bateriasResults) > 0)
                    <div class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                        @foreach($bateriasResults as $bat)
                            <button type="button" wire:click="selectBateria({{ $bat['id'] }})" class="block w-full px-4 py-2 text-left text-sm hover:bg-indigo-50">
                                <span class="font-semibold">{{ $bat['sku'] }}</span> - {{ $bat['marca'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
                <x-input-error for="bateriaSelecionadaId" class="mt-1" />
            </div>
            <div class="md:col-span-5">
                <x-input type="text" wire:model="observacao" class="w-full text-sm" placeholder="Observação da aplicação" />
            </div>
            <div class="md:col-span-2">
                <x-button type="button" wire:click="addAplicacao" class="h-full w-full justify-center">Vincular</x-button>
            </div>
        </div>
    </div>

    <table class="min-w-full divide-y divide-gray-200 border">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">SKU</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Marca</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Observação</th>
                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($aplicacoes as $aplicacao)
                <tr>
                    <td class="px-4 py-3 text-sm font-semibold text-indigo-600">{{ $aplicacao->bateria?->sku }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $aplicacao->bateria?->marca }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $aplicacao->observacao ?: '-' }}</td>
                    <td class="px-4 py-3 text-right text-sm">
                        <button type="button" wire:click="removeAplicacao({{ $aplicacao->id }})" class="text-red-600 hover:text-red-800">Remover</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">Nenhuma aplicação vinculada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
