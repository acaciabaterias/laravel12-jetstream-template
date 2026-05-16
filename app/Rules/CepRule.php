<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CepRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = normalize_digits((string) $value);

        if (strlen($digits) !== 8) {
            $fail('O :attribute informado não é um CEP válido.');
        }
    }
}
