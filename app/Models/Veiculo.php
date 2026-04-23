<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veiculo extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'fabricante_id',
        'modelo',
        'motorizacao',
        'ano_inicio',
        'ano_fim',
        'atributos_dinamicos',
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

    public function baterias()
    {
        return $this->belongsToMany(Bateria::class, 'aplicacoes')
            ->withPivot('observacao')
            ->withTimestamps();
    }
}
