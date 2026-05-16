<?php

namespace App\Livewire;

use App\Models\Cliente;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class TenantManager extends Component
{
    use WithPagination;

    #[Url(as: 'busca')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $tenantId): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-tenants');

        $tenant = Cliente::query()->findOrFail($tenantId);

        $tenant->update([
            'status' => $tenant->status === 'active' ? 'expired' : 'active',
        ]);

        session()->flash('status', "Status do tenant {$tenant->razao_social} atualizado.");
    }

    public function render()
    {
        Gate::forUser(auth('platform')->user())->authorize('view-platform-dashboard');

        $query = Cliente::query()->latest();

        if ($this->search !== '') {
            $query->where(function ($tenantQuery): void {
                $tenantQuery
                    ->where('razao_social', 'like', '%'.$this->search.'%')
                    ->orWhere('cnpj', 'like', '%'.$this->search.'%')
                    ->orWhere('subdominio', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.tenant-manager', [
            'tenants' => $query->paginate(10),
            'stats' => [
                'total' => Cliente::query()->count(),
                'ativos' => Cliente::query()->where('status', 'active')->count(),
                'trial' => Cliente::query()->where('status', 'trial')->count(),
                'expirados' => Cliente::query()->where('status', 'expired')->count(),
            ],
        ]);
    }
}
