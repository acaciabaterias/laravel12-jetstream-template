<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class VehiclePlateRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $plate = strtoupper(trim((string) $value));

        if (! preg_match('/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/', $plate)) {
            $fail('O :attribute informado não é uma placa válida.');
        }
    }
}
