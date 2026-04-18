<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([MultiTenantScope::class])]
class RotaEntrega extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'entregador_id',
        'filial_id',
        'data_rota',
        'veiculo',
        'status',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_rota' => 'date',
        ];
    }

    public function entregador()
    {
        return $this->belongsTo(User::class, 'entregador_id');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function pontos()
    {
        return $this->hasMany(PontoEntrega::class)->orderBy('ordem_parada');
    }
}
