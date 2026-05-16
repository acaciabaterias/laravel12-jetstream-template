<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeolocalizacaoEvento extends Model
{
    use Auditable, HasFactory;

    protected $table = 'geolocalizacao_eventos';

    public $timestamps = false;

    protected $fillable = [
        'rota_entrega_id',
        'ponto_entrega_id',
        'latitude',
        'longitude',
        'tipo_evento',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'recorded_at' => 'datetime',
        ];
    }

    public function rotaEntrega(): BelongsTo
    {
        return $this->belongsTo(RotaEntrega::class, 'rota_entrega_id');
    }

    public function pontoEntrega(): BelongsTo
    {
        return $this->belongsTo(PontoEntrega::class, 'ponto_entrega_id');
    }
}
