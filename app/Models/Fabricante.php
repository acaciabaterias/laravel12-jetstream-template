<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([MultiTenantScope::class])]
class Fabricante extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'nome',
        'codigo',
        'filial_id',
    ];

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }
}
