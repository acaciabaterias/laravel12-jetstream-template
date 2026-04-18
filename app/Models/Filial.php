<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filial extends Model
{
    use HasFactory;

    protected $table = 'filiais';

    protected $fillable = [
        'nome',
        'cnpj',
        'comissao_tipo',
        'comissao_valor',
        'data_fechamento_contabil',
    ];

    protected function casts(): array
    {
        return [
            'data_fechamento_contabil' => 'date',
            'comissao_valor' => 'decimal:2',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
