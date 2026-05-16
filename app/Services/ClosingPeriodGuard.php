<?php

namespace App\Services;

use App\Models\FechamentoContabil;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class ClosingPeriodGuard
{
    public function ensureOpen(CarbonInterface $competencia): void
    {
        $fechado = FechamentoContabil::query()
            ->where('competencia', $competencia->format('Y-m'))
            ->where('status', 'fechado')
            ->exists();

        if ($fechado) {
            throw ValidationException::withMessages([
                'competencia' => 'Esta competencia contabil ja foi fechada e nao aceita alteracoes.',
            ]);
        }
    }
}
