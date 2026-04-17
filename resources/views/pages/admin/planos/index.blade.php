<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\PlanoAssinatura;

new #[Layout('layouts.admin')] class extends Component
{
    public function with(): array
    {
        return [
            'planos' => PlanoAssinatura::all(),
        ];
    }
};

?>

<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold text-gray-900 tracking-tight">
                Planos de Assinatura
            </h2>
            <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-all duration-200">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Novo Plano
            </button>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-fade-in">
        @forelse ($planos as $plano)
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden flex flex-col transition-all hover:shadow-xl hover:-translate-y-1">
                <div class="p-8 pb-0">
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 rounded-full">
                            {{ $plano->slug }}
                        </span>
                        <div class="h-10 w-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $plano->nome }}</h3>
                    <div class="flex items-baseline mb-6">
                        <span class="text-4xl font-extrabold text-gray-900">R$ {{ number_format($plano->preco, 2, ',', '.') }}</span>
                        <span class="text-gray-500 ml-1">/mês</span>
                    </div>
                </div>

                <div class="px-8 py-6 bg-gray-50/50 flex-1 border-t border-gray-100/50">
                    <ul class="space-y-4 mb-8">
                        @foreach(['Usuários ilimitados', 'Suporte prioritário', 'Isolamento de dados físico', 'White Label customizado'] as $feature)
                            <li class="flex items-start">
                                <svg class="flex-shrink-0 h-5 w-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-3 text-sm text-gray-600">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="p-8 pt-0">
                    <button class="w-full py-3 px-4 bg-white border border-gray-200 text-gray-700 font-bold rounded-2xl hover:bg-gray-50 hover:border-indigo-200 hover:text-indigo-600 transition-all">
                        Editar Plano
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center glass rounded-3xl border border-dashed border-gray-300">
                <p class="text-gray-500">Nenhum plano configurado no sistema central.</p>
            </div>
        @endforelse
    </div>
</div>
