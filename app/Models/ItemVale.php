<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemVale extends Model
{
    use HasFactory;

    protected $fillable = [
        'vale_id',
        'bateria_id',
        'quantidade',
        'preco_unitario_original',
        'preco_unitario_final',
        'flag_devolveu_sucata',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'preco_unitario_original' => 'decimal:2',
            'preco_unitario_final' => 'decimal:2',
            'flag_devolveu_sucata' => 'boolean',
        ];
    }

    public function vale()
    {
        return $this->belongsTo(Vale::class);
    }

    public function bateria()
    {
        return $this->belongsTo(Bateria::class);
    }
}
