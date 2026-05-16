<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consentimento extends Model
{
    protected $fillable = [
        'empresa_id',
        'provider_id',
        'banco_nome',
        'banco_codigo',
        'status',
        'access_token_encrypted',
        'refresh_token_encrypted',
        'escopo',
        'expira_em',
    ];

    protected function casts(): array
    {
        return [
            'expira_em' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(BancoProvider::class, 'provider_id');
    }

    public function transacoes(): HasMany
    {
        return $this->hasMany(TransacaoBancaria::class, 'consentimento_id');
    }
}
