<?php

namespace App\Services;

use App\Models\CertificadoDigital;
use Carbon\CarbonImmutable;

class CertificadoDigitalRepository
{
    public function obterAtivoPorFinalidade(int $clienteId, string $finalidade): ?CertificadoDigital
    {
        $hoje = CarbonImmutable::today();

        return CertificadoDigital::query()
            ->where('cliente_id', $clienteId)
            ->where('finalidade', $finalidade)
            ->where('status', 'active')
            ->whereNull('revoked_at')
            ->where(function ($query) use ($hoje) {
                $query->whereNull('validade_fim')
                    ->orWhereDate('validade_fim', '>=', $hoje);
            })
            ->orderByDesc('prioridade')
            ->orderBy('validade_fim')
            ->first();
    }
}
