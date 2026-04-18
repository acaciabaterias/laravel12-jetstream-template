<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([MultiTenantScope::class])]
class RecebimentoMovel extends Model
{
    use HasFactory;

    protected $fillable = [
        'ponto_entrega_id',
        'filial_id',
        'valor',
        'metodo',
        'status_sincronizado',
        'comprovante_url',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
        ];
    }

    public function pontoEntrega()
    {
        return $this->belongsTo(PontoEntrega::class);
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }
}
