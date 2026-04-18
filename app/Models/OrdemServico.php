<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([MultiTenantScope::class])]
class OrdemServico extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'vale_id',
        'cliente_id',
        'filial_id',
        'data_abertura',
        'status',
        'prioridade',
        'tecnico_responsavel',
        'laudo',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_abertura' => 'datetime',
        ];
    }

    public function vale()
    {
        return $this->belongsTo(Vale::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }
}
