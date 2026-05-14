<?php

declare(strict_types=1);

namespace App\Services\Operations;

class AdvancedWhiteLabelTokenValidator
{
    /**
     * @param  array<string, mixed>  $tokens
     * @return array{
     *     passed: bool,
     *     messages: array<int, string>,
     *     normalized_tokens: array<string, string>,
     *     contrast_ratio: float
     * }
     */
    public function validate(array $tokens): array
    {
        $messages = [];
        $normalizedTokens = [];

        foreach ((array) config('advanced_white_label.required_tokens', []) as $tokenKey) {
            $value = $tokens[$tokenKey] ?? null;

            if (! is_string($value) || ! preg_match('/^#?[0-9a-fA-F]{6}$/', $value)) {
                $messages[] = sprintf('Token %s ausente ou invalido.', $tokenKey);

                continue;
            }

            $normalizedTokens[$tokenKey] = '#'.ltrim($value, '#');
        }

        $contrastRatio = 0.0;

        if (isset($normalizedTokens['surface'], $normalizedTokens['text'])) {
            $contrastRatio = $this->contrastRatio($normalizedTokens['surface'], $normalizedTokens['text']);

            if ($contrastRatio < (float) config('advanced_white_label.validation.minimum_contrast_ratio', 4.5)) {
                $messages[] = sprintf('Contraste insuficiente entre surface e text (%.2f).', $contrastRatio);
            }
        }

        return [
            'passed' => $messages === [],
            'messages' => $messages,
            'normalized_tokens' => $normalizedTokens,
            'contrast_ratio' => $contrastRatio,
        ];
    }

    private function contrastRatio(string $backgroundHex, string $foregroundHex): float
    {
        $background = $this->relativeLuminance($backgroundHex);
        $foreground = $this->relativeLuminance($foregroundHex);
        $lighter = max($background, $foreground);
        $darker = min($background, $foreground);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private function relativeLuminance(string $hex): float
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%02x%02x%02x');
        $channels = array_map(function (int $value): float {
            $channel = $value / 255;

            return $channel <= 0.03928
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4;
        }, [$r, $g, $b]);

        return (0.2126 * $channels[0]) + (0.7152 * $channels[1]) + (0.0722 * $channels[2]);
    }
}
