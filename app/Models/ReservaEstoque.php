<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservaEstoque extends Model
{
    use Auditable, HasFactory;

    protected $table = 'reservas_estoque';

    protected $fillable = [
        'vale_id',
        'item_vale_id',
        'bateria_id',
        'deposito_id',
        'quantidade',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
        ];
    }

    public function vale(): BelongsTo
    {
        return $this->belongsTo(Vale::class);
    }

    public function itemVale(): BelongsTo
    {
        return $this->belongsTo(ItemVale::class);
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
