<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserManager extends Component
{
    public $name;

    public $email;

    public $password;

    public $papel;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
        'papel' => 'required|in:dono,gestor,vendedor,tecnico,estoquista',
    ];

    public function createUser()
    {
        // Apenas donos e gestores podem criar usuários
        $this->authorize('gerenciar-usuarios');

        $this->validate();

        // O filial_id é injetado automaticamente a partir do contexto da sessão ou do usuário logado
        $filialId = auth()->user()->isSuperAdmin() ? session('filial_id') : auth()->user()->filial_id;

        if (! $filialId && ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Contexto de filial ausente.');
        }

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'papel' => $this->papel,
            'filial_id' => $filialId,
            'ativo' => true,
        ]);

        $this->reset(['name', 'email', 'password', 'papel']);
    }

    public function render()
    {
        $filialId = auth()->user()->isSuperAdmin() ? session('filial_id') : auth()->user()->filial_id;

        $users = collect();
        if ($filialId || auth()->user()->isSuperAdmin()) {
            $query = User::query();
            if ($filialId) {
                $query->where('filial_id', $filialId);
            }
            $users = $query->get();
        }

        return view('livewire.user-manager', [
            'users' => $users,
        ]);
    }
}
