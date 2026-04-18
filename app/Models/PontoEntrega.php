<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([MultiTenantScope::class])]
#[ObservedBy([\App\Observers\PontoEntregaObserver::class])]
class PontoEntrega extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'rota_entrega_id',
        'vale_id',
        'filial_id',
        'ordem_parada',
        'status',
        'peso_sucata_coletado',
        'latitude',
        'longitude',
        'checkin_at',
        'checkout_at',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'peso_sucata_coletado' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'checkin_at' => 'datetime',
            'checkout_at' => 'datetime',
        ];
    }

    public function rotaEntrega()
    {
        return $this->belongsTo(RotaEntrega::class);
    }

    public function vale()
    {
        return $this->belongsTo(Vale::class);
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function recebimentos()
    {
        return $this->hasMany(RecebimentoMovel::class);
    }
}
