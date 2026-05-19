<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\UsuarioPlataforma;
use Illuminate\Session\Store;

class PlatformLocaleResolutionService
{
    public function __construct(
        private readonly PlatformLocalePublicationService $platformLocalePublicationService,
        private readonly PlatformLocaleResolutionRules $platformLocaleResolutionRules,
    ) {}

    public function resolve(?UsuarioPlataforma $user, Store $session): string
    {
        $activePublication = $this->platformLocalePublicationService->activePublication();

        if ($activePublication === null) {
            return $this->platformLocaleResolutionRules->resolve(
                $user?->preferred_locale ?? $session->get('platform_locale'),
                array_keys((array) config('platform_localization.supported_locales', [])),
                config('platform_localization.default_locale', config('app.locale')),
                config('platform_localization.fallback_locale', config('app.fallback_locale')),
            );
        }

        return $this->platformLocaleResolutionRules->resolve(
            $user?->preferred_locale ?? $session->get('platform_locale'),
            (array) $activePublication->supported_locales,
            (string) $activePublication->default_locale,
            (string) $activePublication->fallback_locale,
        );
    }
}
