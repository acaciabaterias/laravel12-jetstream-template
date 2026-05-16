<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FechamentoContabil extends Model
{
    use Auditable, HasFactory;

    protected $table = 'fechamentos_contabeis';

    protected $fillable = [
        'competencia',
        'status',
        'fechado_em',
        'fechado_por',
    ];

    protected function casts(): array
    {
        return [
            'fechado_em' => 'datetime',
        ];
    }

    public function fechadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fechado_por');
    }
}
