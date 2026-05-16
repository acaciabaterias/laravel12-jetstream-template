<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CnpjRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = normalize_digits((string) $value);

        if (strlen($digits) !== 14 || preg_match('/^(\d)\1{13}$/', $digits) === 1) {
            $fail('O :attribute informado não é um CNPJ válido.');

            return;
        }

        $base = substr($digits, 0, 12);
        $firstDigit = $this->calculateDigit($base, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        $secondDigit = $this->calculateDigit($base.$firstDigit, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        if ($digits !== $base.$firstDigit.$secondDigit) {
            $fail('O :attribute informado não é um CNPJ válido.');
        }
    }

    private function calculateDigit(string $base, array $weights): int
    {
        $sum = 0;

        foreach (str_split($base) as $index => $digit) {
            $sum += ((int) $digit) * $weights[$index];
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
