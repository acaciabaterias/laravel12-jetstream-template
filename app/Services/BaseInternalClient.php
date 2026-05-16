<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

abstract class BaseInternalClient
{
    /**
     * Retorna uma requisição HTTP pré-configurada com a chave de serviço interno.
     */
    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'X-Internal-Service-Key' => config('services.internal_key'),
            'Accept' => 'application/json',
        ]);
    }
}
