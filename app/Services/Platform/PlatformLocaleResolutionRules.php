<?php

declare(strict_types=1);

namespace App\Services\Platform;

class PlatformLocaleResolutionRules
{
    /**
     * @param  array<int, string>  $supportedLocales
     */
    public function resolve(?string $preferredLocale, array $supportedLocales, string $defaultLocale, string $fallbackLocale): string
    {
        if ($preferredLocale !== null && in_array($preferredLocale, $supportedLocales, true)) {
            return $preferredLocale;
        }

        if (in_array($defaultLocale, $supportedLocales, true)) {
            return $defaultLocale;
        }

        if (in_array($fallbackLocale, $supportedLocales, true)) {
            return $fallbackLocale;
        }

        return config('app.fallback_locale', 'en');
    }
}
