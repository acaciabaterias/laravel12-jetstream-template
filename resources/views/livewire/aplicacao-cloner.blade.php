<div>
    <x-dialog-modal wire:model="showModal">
        <x-slot name="title">
            Clonar Aplicações (Baterias)
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <p class="text-sm text-gray-600 mb-4">
                    Selecione um veículo da <strong>mesma montadora</strong> para copiar as baterias compatíveis. Baterias que já estão vinculadas a este veículo serão ignoradas automaticamente para evitar duplicidade.
                </p>

                @if(session()->has('message'))
                    <div class="p-3 bg-green-100 text-green-700 rounded mb-4 text-sm">
                        {{ session('message') }}
                    </div>
                @endif

                <x-label for="origemVeiculoId" value="Veículo de Origem *" />
                <select id="origemVeiculoId" wire:model="origemVeiculoId" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Selecione de onde copiar...</option>
                    @foreach($veiculosCompativeis as $veiculo)
                        <option value="{{ $veiculo->id }}">
                            {{ $veiculo->modelo }} ({{ $veiculo->ano_inicio ?? '...' }} - {{ $veiculo->ano_fim ?? 'Atual' }}) {{ $veiculo->motorizacao }}
                        </option>
                    @endforeach
                </select>
                <x-input-error for="origemVeiculoId" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showModal', false)" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>

            <x-button class="ml-2" wire:click="cloneAplicacoes" wire:loading.attr="disabled" :disabled="empty($origemVeiculoId)">
                Clonar
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
