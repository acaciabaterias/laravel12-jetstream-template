<?php

declare(strict_types=1);

namespace App\Services\Platform;

class PlatformLocalePublicationRules
{
    /**
     * @param  array<int, string>  $supportedLocales
     * @param  array<string, array<string, mixed>>  $coverageSnapshot
     * @return array{passed: bool, messages: array<int, string>}
     */
    public function validate(array $supportedLocales, string $defaultLocale, string $fallbackLocale, array $coverageSnapshot): array
    {
        $messages = [];

        if ($supportedLocales === []) {
            $messages[] = 'A publicacao precisa de pelo menos um locale suportado.';
        }

        if (! in_array($defaultLocale, $supportedLocales, true)) {
            $messages[] = 'O locale padrao precisa estar dentro da lista suportada.';
        }

        if (! in_array($fallbackLocale, $supportedLocales, true)) {
            $messages[] = 'O fallback precisa estar dentro da lista suportada.';
        }

        foreach ($supportedLocales as $locale) {
            if (! array_key_exists($locale, $coverageSnapshot)) {
                $messages[] = sprintf('Cobertura ausente para o locale %s.', $locale);
            }
        }

        return [
            'passed' => $messages === [],
            'messages' => $messages,
        ];
    }
}
