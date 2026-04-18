<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([MultiTenantScope::class])]
class EstoqueSaldo extends Model
{
    use HasFactory;

    protected $table = 'estoque_saldos';

    protected $fillable = [
        'bateria_id',
        'filial_id',
        'deposito_id',
        'quantidade_atual',
        'quantidade_reservada',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_atual' => 'integer',
            'quantidade_reservada' => 'integer',
        ];
    }

    public function bateria()
    {
        return $this->belongsTo(Bateria::class);
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function deposito()
    {
        return $this->belongsTo(Deposito::class);
    }
}
