<div>
    @if(auth()->user()->isSuperAdmin())
        <div class="mb-4">
            <label for="filial_selector" class="block text-sm font-medium text-gray-700">Contexto de Empresa (Filial)</label>
            <select wire:model.live="selectedFilial" id="filial_selector" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">-- Visão Global --</option>
                @foreach($filiais as $filial)
                    <option value="{{ $filial->id }}">{{ $filial->nome }} ({{ $filial->cnpj }})</option>
                @endforeach
            </select>
        </div>
    @endif
</div>
