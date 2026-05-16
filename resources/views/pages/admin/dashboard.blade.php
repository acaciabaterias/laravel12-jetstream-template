<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component
{
    public function with(): array
    {
        return [
            'totalClients' => \App\Models\Cliente::count(),
            'activeSubscriptions' => \App\Models\Cliente::where('status', 'active')->count(),
            'trialSubscriptions' => \App\Models\Cliente::where('status', 'trial')->count(),
        ];
    }
};

?>

<div>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-gray-900 tracking-tight">
            Dashboard da Plataforma
        </h2>
    </x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Clientes Totais -->
        <div class="glass relative overflow-hidden rounded-2xl p-6 shadow-sm ring-1 ring-gray-200/50 transition-all hover:shadow-lg hover:-translate-y-1">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Clientes Totais</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $totalClients }}</h3>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-green-600">
                <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 10l7-7m0 0l7 7m-7-7v18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Instâncias Ativas</span>
            </div>
        </div>

        <!-- Assinaturas Ativas -->
        <div class="glass relative overflow-hidden rounded-2xl p-6 shadow-sm ring-1 ring-gray-200/50 transition-all hover:shadow-lg hover:-translate-y-1">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500/10 text-green-600">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Assinaturas Ativas</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $activeSubscriptions }}</h3>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-gray-500">
                <span>{{ $trialSubscriptions }} em período de trial</span>
            </div>
        </div>

        <!-- Receita Mensal -->
        <div class="glass relative overflow-hidden rounded-2xl p-6 shadow-sm ring-1 ring-gray-200/50 transition-all hover:shadow-lg hover:-translate-y-1">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">MRR Projetado</p>
                    <h3 class="text-2xl font-bold text-gray-900">R$ {{ number_format($activeSubscriptions * 299.90, 2, ',', '.') }}</h3>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-indigo-600">
                <span>Baseado no plano Essential</span>
            </div>
        </div>
    </div>

    <!-- Seção secundária (ex: Clientes Recentes) -->
    <div class="mt-10">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Provisionamentos Recentes</h3>
            <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 transition-colors">Ver todos os clientes</a>
        </div>
        
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subdomínio</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cadastro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse (\App\Models\Cliente::latest()->limit(5)->get() as $cliente)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $cliente->razao_social }}</div>
                                <div class="text-xs text-gray-500">{{ $cliente->cnpj }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                    {{ $cliente->subdominio }}.erp.com
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $cliente->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ ucfirst($cliente->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $cliente->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M19.428 15.341A8 8 0 1112 4v1m0 0L8 7m4-2l4 2m-1.5 5.5l1.5-1.5m0 0l1.5 1.5m-1.5-1.5V14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Nenhum cliente cadastrado ainda.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>