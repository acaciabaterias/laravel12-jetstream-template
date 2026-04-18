<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BateriaEmprestimo extends Model
{
    use HasFactory;

    protected $fillable = [
        'os_garantia_id',
        'bateria_id',
        'data_retirada',
        'data_devolucao_prevista',
        'data_devolucao_real',
        'termo_path',
    ];

    protected function casts(): array
    {
        return [
            'data_retirada' => 'date',
            'data_devolucao_prevista' => 'date',
            'data_devolucao_real' => 'date',
        ];
    }

    public function osGarantia()
    {
        return $this->belongsTo(OrdemServicoGarantia::class);
    }

    public function bateria()
    {
        return $this->belongsTo(Bateria::class);
    }
}
