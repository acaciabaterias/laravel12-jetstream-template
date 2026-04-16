<?php

use App\Models\Filial;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;

new class extends Component
{
    public function with()
    {
        return [
            'filiais' => Filial::where('active', true)->get(),
            'currentFilialId' => Session::get('filial_id'),
        ];
    }

    public function switchFilial($id)
    {
        Session::put('filial_id', $id);
        
        $this->dispatch('filial-switched');
        
        return $this->redirect(request()->header('Referer'), navigate: true);
    }
};
?>

<div>
    <x-dropdown align="right" width="60" dropdown-classes="z-50">
        <x-slot name="trigger">
            <span class="inline-flex rounded-md">
                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-hidden focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                    <svg class="me-2 size-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0112 12.75c-1.393 0-2.651-.59-3.532-1.532A4.833 4.833 0 014.5 10.332V21m15 0h-15" />
                    </svg>
                    
                    @php
                        $currentFilial = $filiais->firstWhere('id', $currentFilialId);
                    @endphp

                    <span class="max-w-[150px] truncate">
                        {{ $currentFilial->nome ?? __('Selecionar Filial') }}
                    </span>

                    <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                    </svg>
                </button>
            </span>
        </x-slot>

        <x-slot name="content">
            <div class="w-60">
                <div class="block px-4 py-2 text-xs text-gray-400">
                    {{ __('Trocar Contexto da Filial') }}
                </div>

                <div class="max-h-64 overflow-y-auto">
                    @foreach ($filiais as $filial)
                        <button 
                            wire:click="switchFilial({{ $filial->id }})" 
                            class="flex items-center justify-between w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-indigo-50 focus:outline-hidden focus:bg-indigo-50 transition duration-150 ease-in-out {{ $currentFilialId == $filial->id ? 'bg-indigo-50/50 font-semibold text-indigo-700' : '' }}"
                            wire:key="filial-{{ $filial->id }}"
                        >
                            <span class="truncate">{{ $filial->nome }}</span>
                            
                            @if($currentFilialId == $filial->id)
                                <svg class="size-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </x-slot>
    </x-dropdown>
</div>