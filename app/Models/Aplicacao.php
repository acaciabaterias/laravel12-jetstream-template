<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([MultiTenantScope::class])]
class Aplicacao extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'aplicacoes';

    protected $fillable = [
        'veiculo_id',
        'bateria_id',
        'observacao',
        'filial_id',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function bateria()
    {
        return $this->belongsTo(Bateria::class);
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }
}
