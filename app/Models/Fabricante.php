<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fabricante extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'codigo',
    ];

    public function veiculos(): HasMany
    {
        return $this->hasMany(Veiculo::class);
    }
}
