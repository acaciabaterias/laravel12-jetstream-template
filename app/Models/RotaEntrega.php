<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RotaEntrega extends Model
{
    use Auditable, HasFactory;

    protected $table = 'rotas_entrega';

    protected $fillable = [
        'entregador_id',
        'data_rota',
        'status',
        'veiculo_id',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_rota' => 'date',
        ];
    }

    public function entregador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entregador_id');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function pontos(): HasMany
    {
        return $this->hasMany(PontoEntrega::class, 'rota_entrega_id')->orderBy('ordem_parada');
    }

    public function geolocalizacaoEventos(): HasMany
    {
        return $this->hasMany(GeolocalizacaoEvento::class, 'rota_entrega_id');
    }
}
