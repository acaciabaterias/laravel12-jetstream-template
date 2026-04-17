<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([MultiTenantScope::class])]
class Veiculo extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'fabricante_id',
        'modelo',
        'motorizacao',
        'ano_inicio',
        'ano_fim',
        'atributos_dinamicos',
        'filial_id',
    ];

    protected function casts(): array
    {
        return [
            'atributos_dinamicos' => 'json',
        ];
    }

    public function fabricante()
    {
        return $this->belongsTo(Fabricante::class);
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function baterias()
    {
        return $this->belongsToMany(Bateria::class, 'aplicacoes')
            ->withPivot('observacao')
            ->withTimestamps();
    }
}
