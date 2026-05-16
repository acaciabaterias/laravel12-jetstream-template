<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PontoEntrega extends Model
{
    use Auditable, HasFactory;

    protected $table = 'pontos_entrega';

    protected $fillable = [
        'rota_entrega_id',
        'vale_id',
        'cliente_id',
        'endereco_entrega',
        'ordem_parada',
        'status',
        'peso_sucata_coletado',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'ordem_parada' => 'integer',
            'peso_sucata_coletado' => 'decimal:2',
        ];
    }

    public function rotaEntrega(): BelongsTo
    {
        return $this->belongsTo(RotaEntrega::class, 'rota_entrega_id');
    }

    public function vale(): BelongsTo
    {
        return $this->belongsTo(Vale::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function recebimentos(): HasMany
    {
        return $this->hasMany(RecebimentoMovel::class, 'ponto_entrega_id');
    }

    public function geolocalizacaoEventos(): HasMany
    {
        return $this->hasMany(GeolocalizacaoEvento::class, 'ponto_entrega_id');
    }
}
