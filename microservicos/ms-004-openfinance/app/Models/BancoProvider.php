<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BancoProvider extends Model
{
    protected $table = 'banco_providers';

    protected $fillable = [
        'nome',
        'codigo_banco',
        'provider',
        'api_client_id',
        'api_client_secret_encrypted',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function consentimentos(): HasMany
    {
        return $this->hasMany(Consentimento::class, 'provider_id');
    }
}
