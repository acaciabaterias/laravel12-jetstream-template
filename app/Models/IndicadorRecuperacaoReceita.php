<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\IndicadorRecuperacaoReceitaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndicadorRecuperacaoReceita extends Model
{
    /** @use HasFactory<IndicadorRecuperacaoReceitaFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'indicadores_recuperacao_receita';

    protected $fillable = [
        'reference_date',
        'channel',
        'stage_name',
        'open_cases',
        'escalated_cases',
        'recovered_cases',
        'broken_promises',
        'recovery_amount',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'reference_date' => 'date',
            'open_cases' => 'integer',
            'escalated_cases' => 'integer',
            'recovered_cases' => 'integer',
            'broken_promises' => 'integer',
            'recovery_amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }
}
