<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserManager extends Component
{
    use AuthorizesRequests;

    public ?int $editingUserId = null;

    public $name;

    public $email;

    public $password;

    public $papel;

    public ?bool $ativo = true;

    public array $availableRoles = [
        'dono' => 'Dono',
        'gestor' => 'Gestor',
        'vendedor' => 'Vendedor',
        'tecnico' => 'Tecnico',
        'estoquista' => 'Estoquista',
        'entregador' => 'Entregador',
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'password' => 'nullable|min:8',
        'papel' => 'required|in:dono,gestor,vendedor,tecnico,estoquista,entregador',
        'ativo' => 'required|boolean',
    ];

    public function createUser(): void
    {
        $this->authorize('create', User::class);

        $this->validate($this->rulesForCreate());

        $filialId = $this->resolveManagedFilialId();

        if (! $filialId) {
            abort(403, 'Contexto de filial ausente.');
        }

        $createdUser = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'papel' => $this->papel,
            'filial_id' => $filialId,
            'ativo' => (bool) $this->ativo,
        ]);

        $this->writeAuditLog(
            action: 'created',
            targetUser: $createdUser,
            oldValues: null,
            newValues: $createdUser->only(['name', 'email', 'papel', 'ativo', 'filial_id']),
        );

        $this->resetForm();
    }

    public function editUser(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $this->authorize('update', $user);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->papel = $user->papel;
        $this->ativo = (bool) $user->ativo;
        $this->password = null;
    }

    public function updateUser(): void
    {
        $user = User::query()->findOrFail($this->editingUserId);

        $this->authorize('update', $user);

        $this->validate($this->rulesForUpdate($user));

        $oldValues = $user->only(['name', 'email', 'papel', 'ativo']);

        $payload = [
            'name' => $this->name,
            'email' => $this->email,
            'papel' => $this->papel,
            'ativo' => (bool) $this->ativo,
        ];

        if (filled($this->password)) {
            $payload['password'] = Hash::make($this->password);
        }

        $user->update($payload);

        $this->writeAuditLog(
            action: 'updated',
            targetUser: $user->fresh(),
            oldValues: $oldValues,
            newValues: $user->fresh()->only(['name', 'email', 'papel', 'ativo']),
        );

        $this->resetForm();
    }

    public function toggleActive(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $this->authorize('toggleActive', $user);

        $oldValues = $user->only(['ativo']);

        $user->forceFill([
            'ativo' => ! $user->ativo,
        ])->save();

        $this->writeAuditLog(
            action: $user->ativo ? 'activated' : 'deactivated',
            targetUser: $user,
            oldValues: $oldValues,
            newValues: $user->only(['ativo']),
        );
    }

    public function cancelEditing(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        $this->authorize('viewAny', User::class);

        $filialId = $this->resolveManagedFilialId();

        $query = User::query()->orderBy('name');

        if ($filialId) {
            $query->where('filial_id', $filialId);
        }

        $users = $query->get();

        return view('livewire.user-manager', [
            'users' => $users,
        ]);
    }

    protected function rulesForCreate(): array
    {
        return [
            ...$this->rules,
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8'],
        ];
    }

    protected function rulesForUpdate(User $user): array
    {
        return [
            ...$this->rules,
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ];
    }

    protected function resolveManagedFilialId(): ?int
    {
        return auth()->user()->isSuperAdmin()
            ? session('filial_id')
            : auth()->user()->filial_id;
    }

    protected function resetForm(): void
    {
        $this->reset(['editingUserId', 'name', 'email', 'password', 'papel', 'ativo']);
        $this->resetValidation();
        $this->ativo = true;
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
}
