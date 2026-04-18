<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndiceRetorno extends Model
{
    use HasFactory;

    protected $fillable = [
        'bateria_id',
        'periodo',
        'total_vendidas',
        'total_garantias',
        'indice_calculado',
    ];

    public function bateria()
    {
        return $this->belongsTo(Bateria::class);
    }
}
