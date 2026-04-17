<div class="p-6 bg-white overflow-hidden shadow-xl sm:rounded-lg max-w-5xl mx-auto">
    <h2 class="text-xl font-semibold text-gray-800 mb-6 border-b pb-4">Importação de XML de NF-e</h2>

    @if(session()->has('message'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">
            <span class="font-medium">Sucesso!</span> {{ session('message') }}
        </div>
    @endif

    @error('geral')
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
            {{ $message }}
        </div>
    @enderror

    @if(!$isProcessing)
        <!-- Upload State -->
        <div class="space-y-6">
            <p class="text-sm text-gray-600">Para automatizar a entrada no estoque, faça o upload do XML emitido pelo fabricante.</p>
            
            <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-gray-50 flex-col items-center
                {{ $xmlFile ? 'border-green-400 bg-green-50' : 'border-gray-300' }}
            ">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="mt-4 flex text-sm text-gray-600 justify-center">
                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500 p-2 border shadow-sm">
                        <span>Upload do XML</span>
                        <input id="file-upload" wire:model="xmlFile" name="file-upload" type="file" class="sr-only" accept=".xml">
                    </label>
                </div>
                
                @if($xmlFile)
                    <p class="mt-3 text-sm text-green-700 font-semibold">{{ $xmlFile->getClientOriginalName() }} processado em cache.</p>
                @else
                    <p class="text-xs text-gray-500 mt-2">Apenas XML até 10MB</p>
                @endif
                
                @error('xmlFile') <span class="text-red-500 text-sm mt-2 font-bold">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end pt-4">
                <x-button wire:click="processarXML" wire:loading.attr="disabled" :disabled="!$xmlFile">
                    <span wire:loading.remove wire:target="processarXML">Ler e Analisar XML</span>
                    <span wire:loading wire:target="processarXML">Lendo...</span>
                </x-button>
            </div>
        </div>
    @else
        <!-- Mapping State -->
        <div class="space-y-6">
            <div class="bg-indigo-50 p-4 rounded border border-indigo-200">
                <h3 class="font-bold text-indigo-800">Resumo da Nota Fiscal</h3>
                <div class="grid grid-cols-2 gap-4 mt-2 text-sm text-indigo-900">
                    <div><span class="font-semibold">Chave:</span> {{ $parsedData['chave'] }}</div>
                    <div><span class="font-semibold">Fornecedor:</span> {{ $parsedData['fornecedor']['nome'] }} (CNPJ: {{ $parsedData['fornecedor']['cnpj'] }})</div>
                </div>
            </div>

            <div>
                <x-label for="deposito_id" value="Qual o destino físico dessas baterias?" class="text-lg font-bold" />
                <select id="deposito_id" wire:model="deposito_id" class="mt-2 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Selecione o depósito...</option>
                    @foreach($depositos as $dep)
                        <option value="{{ $dep->id }}">{{ $dep->nome }}</option>
                    @endforeach
                </select>
                @error('deposito_id') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            @error('mapeamento')
                <div class="p-3 bg-red-100 text-red-800 rounded text-sm font-semibold">{{ $message }}</div>
            @enderror

            <div class="overflow-x-auto border rounded-xl">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Item na Nota (XML)</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase w-24">Qtd</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Vincular Produto Interno (De/Para)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($parsedData['itens'] as $index => $itemOriginal)
                            <tr class="{{ empty($mappedItems[$index]['bateria_id']) ? 'bg-yellow-50' : 'bg-white' }}">
                                <td class="px-4 py-4 align-top">
                                    <div class="font-bold text-gray-800">{{ $itemOriginal['nome'] }}</div>
                                    <div class="text-xs text-gray-500">
                                        Cód: {{ $itemOriginal['codigo_fornecedor'] }} 
                                        @if($itemOriginal['ean']) | EAN: {{ $itemOriginal['ean'] }} @endif
                                    </div>
                                    <div class="text-xs font-semibold text-gray-600 mt-1">R$ {{ number_format($itemOriginal['valor_unitario'], 2, ',', '.') }} un</div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <x-input type="number" wire:model="mappedItems.{{ $index }}.quantidade" class="w-full h-9 text-sm" min="0" />
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <select wire:model="mappedItems.{{ $index }}.bateria_id" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm h-9 text-sm">
                                        <option value="">-- Mapeamento Pendente --</option>
                                        @foreach($bateriasInternas as $id => $nome)
                                            <option value="{{ $id }}">{{ $nome }}</option>
                                        @endforeach
                                    </select>
                                    @if(empty($mappedItems[$index]['bateria_id']))
                                        <span class="text-yellow-600 text-xs mt-1 block">Necessário vincular produto correspondente na base.</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center pt-6 border-t">
                <x-secondary-button wire:click="cancel">Cancelar / Novo XML</x-secondary-button>
                <x-button wire:click="finalizarImportacao" class="bg-green-600 hover:bg-green-700" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="finalizarImportacao">Finalizar Importação</span>
                    <span wire:loading wire:target="finalizarImportacao">Processando e Gravando Estoque...</span>
                </x-button>
            </div>
        </div>
    @endif
</div>
