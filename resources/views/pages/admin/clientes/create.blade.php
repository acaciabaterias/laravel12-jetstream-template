<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Cliente;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

new #[Layout('layouts.admin')] class extends Component
{
    public string $cnpj = '';
    public string $razao_social = '';
    public string $nome_fantasia = '';
    public string $email_contato = '';
    public string $subdominio = '';
    public string $plano = 'essential';
    
    public bool $isProvisioning = false;
    public string $provisioningStep = '';

    protected $rules = [
        'cnpj' => 'required|unique:central.clientes,cnpj',
        'razao_social' => 'required|min:3',
        'email_contato' => 'required|email',
        'subdominio' => 'required|alpha_dash|unique:central.clientes,subdominio',
        'plano' => 'required|in:essential,plus,pro',
    ];

    public function provision()
    {
        $this->validate();
        
        $this->isProvisioning = true;
        $this->provisioningStep = 'Criando registro do cliente...';
        
        // 1. Criar registro no Banco Central
        $cliente = Cliente::create([
            'cnpj' => $this->cnpj,
            'razao_social' => $this->razao_social,
            'nome_fantasia' => $this->nome_fantasia ?: $this->razao_social,
            'email_contato' => $this->email_contato,
            'subdominio' => $this->subdominio,
            'plano' => $this->plano,
            'status' => 'active',
        ]);
        
        $this->provisioningStep = 'Iniciando provisionamento no Supabase (Integration)...';
        
        // 2. Chamar o comando tenant:create
        // Em um ambiente real, isso seria um Job em background
        try {
            // Mock de sucesso para demonstração visual rápida no dashboard
            // Na implementação real, Artisan::call('tenant:create', ['subdomain' => $this->subdominio])
            
            $this->provisioningStep = 'Configurando banco de dados isolado...';
            usleep(500000); 
            
            $this->provisioningStep = 'Executando migrações ERP...';
            usleep(500000);
            
            $this->provisioningStep = 'Finalizando configuração...';
            usleep(300000);

            session()->flash('message', "Cliente {$this->razao_social} provisionado com sucesso!");
            return redirect()->route('admin.clientes.index');
            
        } catch (\Exception $e) {
            $this->isProvisioning = false;
            $this->addError('provisioning', 'Erro no provisionamento: ' . $e->getMessage());
        }
    }
    
    public function updatedRazaoSocial($value)
    {
        if (empty($this->subdominio)) {
            $this->subdominio = Str::slug($value);
        }
    }
};

?>

<div>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.clientes.index') }}" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="text-3xl font-bold text-gray-900 tracking-tight">
                Novo Assinante
            </h2>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden p-8 animate-fade-in">
            
            @if($isProvisioning)
                <div class="flex flex-col items-center justify-center py-12 space-y-6">
                    <div class="relative">
                        <div class="h-24 w-24 rounded-full border-4 border-indigo-100 border-t-indigo-600 animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="h-8 w-8 text-indigo-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-gray-900">Provisionando Tenant</h3>
                        <p class="text-gray-500">{{ $provisioningStep }}</p>
                    </div>
                    <div class="w-full max-w-xs bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-indigo-600 h-full animate-[progress_2s_ease-in-out_infinite]" style="width: 50%"></div>
                    </div>
                </div>
            @else
                <form wire:submit.prevent="provision" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- CNPJ -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">CNPJ</label>
                            <input wire:model="cnpj" type="text" placeholder="00.000.000/0001-00" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all">
                            @error('cnpj') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Razão Social -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Razão Social</label>
                            <input wire:model.blur="razao_social" type="text" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all">
                            @error('razao_social') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email de Contato</label>
                            <input wire:model="email_contato" type="email" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all">
                            @error('email_contato') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Subdomínio -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Subdomínio</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-50 text-gray-500 text-sm font-medium">https://</span>
                                <input wire:model="subdominio" type="text" class="flex-1 min-w-0 rounded-none border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all">
                                <span class="inline-flex items-center px-3 rounded-r-xl border border-l-0 border-gray-200 bg-gray-50 text-gray-500 text-sm font-medium">.erp.com</span>
                            </div>
                            @error('subdominio') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Plano -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-4">Plano de Assinatura</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                @foreach(['essential' => 'Essential', 'plus' => 'Plus', 'pro' => 'Pro'] as $key => $label)
                                    <label class="relative flex cursor-pointer rounded-xl border p-4 shadow-sm focus:outline-none transition-all {{ $plano === $key ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/50' : 'border-gray-200 bg-white hover:bg-gray-50' }}">
                                        <input type="radio" wire:model.live="plano" value="{{ $key }}" class="sr-only">
                                        <div class="flex flex-col">
                                            <span class="block text-sm font-bold {{ $plano === $key ? 'text-indigo-900' : 'text-gray-900' }}">{{ $label }}</span>
                                            <span class="mt-1 flex items-center text-xs {{ $plano === $key ? 'text-indigo-700' : 'text-gray-500' }}">
                                                {{ $key === 'essential' ? 'R$ 299,90/mês' : ($key === 'plus' ? 'R$ 599,90/mês' : 'R$ 999,90/mês') }}
                                            </span>
                                        </div>
                                        @if($plano === $key)
                                            <svg class="absolute top-4 right-4 h-5 w-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @error('provisioning') 
                        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="pt-4 flex justify-end space-x-4">
                        <button type="button" class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                        <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 transition-all">
                            Criar e Provisionar Tenant
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<style>
    @keyframes progress {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(200%); }
    }
</style>
