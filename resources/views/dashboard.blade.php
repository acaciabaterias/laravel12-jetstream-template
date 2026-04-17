<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- T021: Seletor de Filial (visível apenas para super_admin) --}}
            @if(auth()->user()->isSuperAdmin())
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Contexto de Empresa</h3>
                    <livewire:filial-selector />
                </div>
            @endif

            {{-- T022 & T023: Listagem e criação de usuários (dono/gestor/super_admin) --}}
            @if(auth()->user()->hasRole(['dono', 'gestor', 'super_admin']))
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Gerenciamento de Usuários</h3>
                    <livewire:user-manager />
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <x-welcome />
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
