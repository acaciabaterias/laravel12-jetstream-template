<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UsuarioPlataforma extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $table = 'usuarios_plataforma';

    protected $fillable = [
        'name',
        'email',
        'password',
        'papel',
        'ativo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'ativo' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->papel === 'super_admin';
    }

    public function hasRole(string|array $papel): bool
    {
        if (is_array($papel)) {
            return in_array($this->papel, $papel, true);
        }

        return $this->papel === $papel;
    }
}
