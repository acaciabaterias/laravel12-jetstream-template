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

            @can('acesso-estoque')
                <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
                    <livewire:estoque-dashboard />
                    <livewire:estoque-adjustment-form />
                </div>

                <div class="grid gap-6 xl:grid-cols-2">
                    <livewire:xml-import-form />
                    <livewire:conta-sucata-dashboard />
                </div>
            @endcan

            @can('acesso-vendas')
                <div class="grid gap-6 xl:grid-cols-[1.35fr_0.95fr]">
                    <livewire:vale-form />
                    <div class="space-y-6">
                        <livewire:vale-conversion-actions />
                        @can('acesso-tecnico')
                            <livewire:ordem-servico-form />
                        @endcan
                    </div>
                </div>

                <livewire:vale-list />
            @endcan

            @can('acesso-logistica')
                <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                    <livewire:route-planner />
                    <livewire:logistics-dashboard />
                </div>

                <livewire:delivery-route-screen />
            @endcan

            @can('acesso-tecnico')
                <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                    <livewire:garantia-board />
                    <livewire:garantia-form />
                </div>

                <livewire:garantia-laudo-form />
            @endcan

            @can('acesso-financeiro')
                <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                    <livewire:finance-dashboard />
                    <livewire:cash-flow-panel />
                </div>

                <livewire:margin-analysis-grid />

                <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                    <livewire:fiscal-contingency-dashboard />
                    <livewire:cnab-upload-panel />
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
