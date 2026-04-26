<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BancoPerfil extends Model
{
    protected $table = 'banco_perfils';

    protected $fillable = [
        'nome',
        'codigo_banco',
        'agencia',
        'conta',
        'convenio',
        'ambiente',
        'credenciais_json_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'credenciais_json_encrypted' => 'array',
        ];
    }

    public function cobrancas(): HasMany
    {
        return $this->hasMany(Cobranca::class, 'banco_id');
    }
}
