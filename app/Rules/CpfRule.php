<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = normalize_digits((string) $value);

        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            $fail('O :attribute informado não é um CPF válido.');

            return;
        }

        $base = substr($digits, 0, 9);
        $firstDigit = $this->calculateDigit($base, 10);
        $secondDigit = $this->calculateDigit($base.$firstDigit, 11);

        if ($digits !== $base.$firstDigit.$secondDigit) {
            $fail('O :attribute informado não é um CPF válido.');
        }
    }

    private function calculateDigit(string $base, int $weightStart): int
    {
        $sum = 0;
        $weight = $weightStart;

        foreach (str_split($base) as $digit) {
            $sum += ((int) $digit) * $weight;
            $weight--;
        }

        $remainder = ($sum * 10) % 11;

        return $remainder === 10 ? 0 : $remainder;
    }
}
