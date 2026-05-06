<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserForm extends Component
{
    use AuthorizesRequests;

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $papel = '';

    public bool $ativo = true;

    public array $availableRoles = [
        'dono' => 'Dono',
        'gestor' => 'Gestor',
        'vendedor' => 'Vendedor',
        'tecnico' => 'Tecnico',
        'estoquista' => 'Estoquista',
        'entregador' => 'Entregador',
    ];

    public function mount(?int $userId = null): void
    {
        $this->userId = $userId;

        if ($userId !== null) {
            $user = User::query()->findOrFail($userId);
            $this->authorize('update', $user);

            $this->name = $user->name;
            $this->email = $user->email;
            $this->papel = $user->papel;
            $this->ativo = (bool) $user->ativo;
        } else {
            $this->authorize('create', User::class);
        }
    }

    public function save(): void
    {
        if ($this->userId !== null) {
            $user = User::query()->findOrFail($this->userId);
            $this->authorize('update', $user);

            $this->validate($this->rulesForUpdate($user));

            $oldValues = $user->only(['name', 'email', 'papel', 'ativo']);
            $payload = [
                'name' => $this->name,
                'email' => $this->email,
                'papel' => $this->papel,
                'ativo' => $this->ativo,
            ];

            if ($this->password !== '') {
                $payload['password'] = Hash::make($this->password);
            }

            $user->update($payload);
            $this->writeAuditLog('updated', $user, $oldValues, $user->fresh()->only(['name', 'email', 'papel', 'ativo']));
        } else {
            $this->authorize('create', User::class);

            $this->validate($this->rulesForCreate());

            $filialId = auth()->user()->isSuperAdmin() ? session('filial_id') : auth()->user()->filial_id;
            if (! $filialId) {
                abort(403, 'Contexto de filial ausente.');
            }

            $createdUser = User::query()->create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'papel' => $this->papel,
                'filial_id' => $filialId,
                'ativo' => $this->ativo,
            ]);

            $this->writeAuditLog('created', $createdUser, null, $createdUser->only(['name', 'email', 'papel', 'ativo', 'filial_id']));
        }

        session()->flash('status', 'Usuário salvo com sucesso.');
    }

    protected function rulesForCreate(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'papel' => ['required', 'in:dono,gestor,vendedor,tecnico,estoquista,entregador'],
            'ativo' => ['required', 'boolean'],
        ];
    }

    protected function rulesForUpdate(User $user): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'papel' => ['required', 'in:dono,gestor,vendedor,tecnico,estoquista,entregador'],
            'ativo' => ['required', 'boolean'],
        ];
    }

    protected function writeAuditLog(string $action, User $targetUser, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'table_name' => 'users',
            'record_id' => $targetUser->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public function render()
    {
        return view('livewire.user-form');
    }
}
