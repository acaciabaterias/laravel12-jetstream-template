<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\UsuarioPlataforma;
use Illuminate\Session\Store;
use InvalidArgumentException;

class PlatformCurrencyPreferenceService
{
    public function __construct(
        private readonly PlatformCurrencyPublicationService $platformCurrencyPublicationService,
    ) {}

    /**
     * @return array<int, string>
     */
    public function supportedCurrencies(): array
    {
        $activePublication = $this->platformCurrencyPublicationService->activePublication();

        if ($activePublication !== null) {
            return (array) $activePublication->supported_currencies;
        }

        return array_keys((array) config('platform_currencies.supported_currencies', []));
    }

    public function updatePreference(UsuarioPlataforma $user, string $currencyCode, Store $session): UsuarioPlataforma
    {
        if (! in_array($currencyCode, $this->supportedCurrencies(), true)) {
            throw new InvalidArgumentException('Moeda nao suportada para a plataforma.');
        }

        $user->forceFill([
            'preferred_currency' => $currencyCode,
        ])->save();

        $session->put('platform_currency', $currencyCode);

        return $user->refresh();
    }
}
