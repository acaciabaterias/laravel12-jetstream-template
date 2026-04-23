<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdemServico extends Model
{
    use Auditable, HasFactory;

    protected $table = 'ordens_servico';

    protected $fillable = [
        'vale_id',
        'cliente_id',
        'tecnico_responsavel_id',
        'data_abertura',
        'status',
        'laudo',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_abertura' => 'datetime',
        ];
    }

    public function vale(): BelongsTo
    {
        return $this->belongsTo(Vale::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tecnicoResponsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_responsavel_id');
    }
}
