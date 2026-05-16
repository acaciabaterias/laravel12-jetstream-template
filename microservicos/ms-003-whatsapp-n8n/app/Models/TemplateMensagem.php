<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateMensagem extends Model
{
    protected $table = 'template_mensagens';

    protected $fillable = [
        'nome',
        'canal',
        'conteudo_template',
        'variaveis',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'variaveis' => 'array',
            'ativo' => 'boolean',
        ];
    }
}
