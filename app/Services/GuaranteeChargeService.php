<?php

namespace App\Services;

use App\Models\OrdemServicoGarantia;

class GuaranteeChargeService
{
    public function generateImprocedenteCharge(OrdemServicoGarantia $ordemServicoGarantia, float $valor): void
    {
        if ($ordemServicoGarantia->cobranca_valor && (float) $ordemServicoGarantia->cobranca_valor > 0) {
            return;
        }

        $ordemServicoGarantia->update([
            'resultado' => 'improcedente',
            'status' => 'aguardando_pagamento',
            'cobranca_valor' => $valor,
        ]);
    }
}
