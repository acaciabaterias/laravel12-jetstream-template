<?php

namespace App\Services;

use App\Models\BoletoOrquestrado;
use App\Models\NotaFiscalOrquestrada;
use App\Models\Vale;

class OrchestratorIdempotencyService
{
    public function forFiscal(Vale $vale): string
    {
        return sha1('fiscal-'.$vale->id);
    }

    public function forBank(Vale $vale): string
    {
        return sha1('bank-'.$vale->id);
    }

    public function alreadyProcessedFiscal(string $key): bool
    {
        return NotaFiscalOrquestrada::query()->where('idempotency_key', $key)->exists();
    }

    public function alreadyProcessedBank(string $key): bool
    {
        return BoletoOrquestrado::query()->where('idempotency_key', $key)->exists();
    }
}
