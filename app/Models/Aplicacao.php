<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aplicacao extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'aplicacoes';

    protected $fillable = [
        'veiculo_id',
        'bateria_id',
        'observacao',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function bateria()
    {
        return $this->belongsTo(Bateria::class);
    }
}
