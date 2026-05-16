<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BateriaEmprestimo extends Model
{
    use Auditable, HasFactory;

    protected $table = 'baterias_emprestimo';

    protected $fillable = [
        'os_garantia_id',
        'bateria_usada_id',
        'data_retirada',
        'data_devolucao_prevista',
        'data_devolucao_real',
        'termo_arquivo_path',
    ];

    protected function casts(): array
    {
        return [
            'data_retirada' => 'datetime',
            'data_devolucao_prevista' => 'datetime',
            'data_devolucao_real' => 'datetime',
        ];
    }

    public function ordemServicoGarantia(): BelongsTo
    {
        return $this->belongsTo(OrdemServicoGarantia::class, 'os_garantia_id');
    }

    public function bateriaUsada(): BelongsTo
    {
        return $this->belongsTo(Bateria::class, 'bateria_usada_id');
    }
}
