<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UsuarioPlataforma extends Authenticatable
{
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

    protected $casts = [
        'password' => 'hashed',
        'ativo' => 'boolean',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->papel === 'super_admin';
    }
}
