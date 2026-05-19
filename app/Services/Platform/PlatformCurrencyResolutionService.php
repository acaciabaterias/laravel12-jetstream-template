<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\UsuarioPlataforma;
use Illuminate\Session\Store;

class PlatformCurrencyResolutionService
{
    public function __construct(
        private readonly PlatformCurrencyPublicationService $platformCurrencyPublicationService,
        private readonly PlatformCurrencyResolutionRules $platformCurrencyResolutionRules,
    ) {}

    public function resolve(?UsuarioPlataforma $user, Store $session): string
    {
        $activePublication = $this->platformCurrencyPublicationService->activePublication();

        if ($activePublication === null) {
            return $this->platformCurrencyResolutionRules->resolve(
                $user?->preferred_currency ?? $session->get('platform_currency'),
                array_keys((array) config('platform_currencies.supported_currencies', [])),
                config('platform_currencies.default_currency', 'BRL'),
                config('platform_currencies.base_currency', 'BRL'),
            );
        }

        return $this->platformCurrencyResolutionRules->resolve(
            $user?->preferred_currency ?? $session->get('platform_currency'),
            (array) $activePublication->supported_currencies,
            (string) $activePublication->default_currency_code,
            (string) $activePublication->base_currency_code,
        );
    }
}
