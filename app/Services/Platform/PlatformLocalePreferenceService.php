<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\UsuarioPlataforma;
use Illuminate\Session\Store;
use InvalidArgumentException;

class PlatformLocalePreferenceService
{
    /**
     * @return array<int, string>
     */
    public function supportedLocales(): array
    {
        return array_keys((array) config('platform_localization.supported_locales', []));
    }

    public function updatePreference(UsuarioPlataforma $user, string $locale, Store $session): UsuarioPlataforma
    {
        if (! in_array($locale, $this->supportedLocales(), true)) {
            throw new InvalidArgumentException('Locale nao suportado para a plataforma.');
        }

        $user->forceFill([
            'preferred_locale' => $locale,
        ])->save();

        $session->put('platform_locale', $locale);

        return $user->refresh();
    }
}
