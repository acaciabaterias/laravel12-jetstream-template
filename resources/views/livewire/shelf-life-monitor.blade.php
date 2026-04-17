<div class="p-6 bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 border-b pb-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Monitor de Shelf Life</h2>
            <p class="text-sm text-gray-500 mt-1">Identifica baterias em estoque há muito tempo sem receber carga ou movimentação de entrada.</p>
        </div>
        <div class="flex items-center space-x-2">
            <x-label for="diasLimite" value="Alerta a partir de (Dias):" class="whitespace-nowrap" />
            <select id="diasLimite" wire:model.live="diasLimite" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                <option value="30">30 Dias</option>
                <option value="60">60 Dias</option>
                <option value="90">90 Dias</option>
                <option value="120">120 Dias</option>
            </select>
        </div>
    </div>

    @if(count($alertas) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($alertas as $saldo)
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-bold text-red-800">{{ $saldo->bateria->sku }}</h3>
                            <p class="text-sm font-medium text-red-600">{{ $saldo->bateria->marca }} ({{ $saldo->bateria->tecnologia }})</p>
                        </div>
                        <span class="inline-flex items-center justify-center px-3 py-1 text-sm font-bold leading-none text-red-100 bg-red-600 rounded-full">
                            {{ $saldo->dias_estagnado }} Dias
                        </span>
                    </div>
                    
                    <div class="mt-4 pt-3 border-t border-red-200 grid grid-cols-2 gap-2 text-sm text-red-800">
                        <div>
                            <span class="block text-xs uppercase opacity-75">Depósito</span>
                            <strong>{{ $saldo->deposito->nome }}</strong>
                        </div>
                        <div>
                            <span class="block text-xs uppercase opacity-75">Qtd Disponível</span>
                            <strong>{{ $saldo->quantidade_atual }} un</strong>
                        </div>
                        <div class="col-span-2 mt-1">
                            <span class="block text-xs uppercase opacity-75">Última Entrada</span>
                            <strong>{{ \Carbon\Carbon::parse($saldo->data_ultima_entrada)->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-10">
            <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Estoque Saudável</h3>
            <p class="mt-1 text-sm text-gray-500">Nenhum produto encontra-se estagnado além de {{ $diasLimite }} dias no momento.</p>
        </div>
    @endif
</div>
