<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\PlanoAssinatura;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class TenantForm extends Component
{
    public ?Cliente $tenant = null;

    public ?int $tenantId = null;

    public string $cnpj = '';

    public string $razaoSocial = '';

    public string $nomeFantasia = '';

    public string $emailContato = '';

    public string $telefone = '';

    public string $subdominio = '';

    public string $plano = 'essential';

    public string $status = 'trial';

    public function mount(?Cliente $tenant = null): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-tenants');

        $this->tenant = $tenant;

        if ($tenant instanceof Cliente && $tenant->exists) {
            $this->tenantId = $tenant->id;
            $this->cnpj = $tenant->cnpj ?? '';
            $this->razaoSocial = $tenant->razao_social ?? '';
            $this->nomeFantasia = $tenant->nome_fantasia ?? '';
            $this->emailContato = $tenant->email_contato ?? '';
            $this->telefone = $tenant->telefone ?? '';
            $this->subdominio = $tenant->subdominio ?? '';
            $this->plano = $tenant->plano ?? 'essential';
            $this->status = $tenant->status ?? 'trial';
        }
    }

    public function updatedRazaoSocial(string $value): void
    {
        if ($this->subdominio === '') {
            $this->subdominio = Str::slug($value);
        }
    }

    public function save()
    {
        Gate::forUser(auth('platform')->user())->authorize(
            $this->tenantId !== null ? 'update' : 'create',
            $this->tenantId !== null ? $this->tenant : Cliente::class,
        );

        $validated = $this->validate($this->rules());

        $payload = [
            'cnpj' => $validated['cnpj'],
            'razao_social' => $validated['razaoSocial'],
            'nome_fantasia' => $validated['nomeFantasia'] ?: $validated['razaoSocial'],
            'email_contato' => $validated['emailContato'],
            'telefone' => $validated['telefone'] ?: null,
            'subdominio' => $validated['subdominio'],
            'plano' => $validated['plano'],
            'status' => $validated['status'],
        ];

        if ($this->tenantId !== null) {
            $this->tenant?->update($payload);
            $message = 'Tenant atualizado com sucesso.';
        } else {
            Cliente::query()->create([
                ...$payload,
                'supabase_project_ref' => Str::lower(Str::random(20)),
                'supabase_url' => 'https://pending-provision.supabase.co',
                'supabase_db_host' => 'pending-provision.supabase.co',
                'supabase_db_password' => Str::random(8),
                'supabase_anon_key' => Str::random(64),
                'supabase_service_role_key' => Str::random(64),
            ]);
            $message = 'Tenant criado com sucesso.';
        }

        session()->flash('status', $message);

        return redirect()->route('admin.clientes.index');
    }

    public function render()
    {
        return view('livewire.tenant-form', [
            'planOptions' => PlanoAssinatura::query()->orderBy('preco_mensal')->get(),
            'statusOptions' => [
                'trial' => 'Trial',
                'active' => 'Ativo',
                'expired' => 'Expirado',
                'cancelled' => 'Cancelado',
            ],
        ]);
    }

    protected function rules(): array
    {
        return [
            'cnpj' => ['required', 'string', 'max:18', Rule::unique('clientes', 'cnpj')->ignore($this->tenantId)],
            'razaoSocial' => ['required', 'string', 'min:3', 'max:150'],
            'nomeFantasia' => ['nullable', 'string', 'max:100'],
            'emailContato' => ['required', 'email', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'subdominio' => ['required', 'alpha_dash', 'max:50', Rule::unique('clientes', 'subdominio')->ignore($this->tenantId)],
            'plano' => ['required', 'string', 'max:30'],
            'status' => ['required', Rule::in(['trial', 'active', 'expired', 'cancelled'])],
        ];
    }
}
