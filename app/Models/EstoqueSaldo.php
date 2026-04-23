<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstoqueSaldo extends Model
{
    use HasFactory;

    protected $fillable = [
        'bateria_id',
        'deposito_id',
        'quantidade_atual',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_atual' => 'integer',
        ];
    }

    public function bateria(): BelongsTo
    {
        return $this->belongsTo(Bateria::class);
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class);
    }
}
