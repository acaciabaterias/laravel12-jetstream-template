<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemessaCNAB extends Model
{
    protected $table = 'remessa_cnabs';

    protected $fillable = [
        'banco_id',
        'arquivo_nome',
        'arquivo_base64',
        'tipo',
        'status',
        'registros_total',
        'registros_ok',
        'registros_erro',
    ];

    protected function casts(): array
    {
        return [
            'registros_total' => 'integer',
            'registros_ok' => 'integer',
            'registros_erro' => 'integer',
        ];
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(BancoPerfil::class, 'banco_id');
    }
}
