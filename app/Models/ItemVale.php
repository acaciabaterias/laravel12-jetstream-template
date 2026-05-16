<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemVale extends Model
{
    use Auditable, HasFactory;

    protected $table = 'itens_vale';

    protected $fillable = [
        'vale_id',
        'bateria_id',
        'quantidade',
        'preco_unitario_original',
        'preco_unitario_final',
        'flag_devolveu_sucata',
        'observacao',
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

    public function vale(): BelongsTo
    {
        return $this->belongsTo(Vale::class);
    }

    public function bateria(): BelongsTo
    {
        return $this->belongsTo(Bateria::class);
    }
}
