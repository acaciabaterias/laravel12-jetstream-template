<?php

declare(strict_types=1);

if (! function_exists('normalize_digits')) {
    function normalize_digits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }
}

if (! function_exists('format_cnpj')) {
    function format_cnpj(?string $value): string
    {
        $digits = normalize_digits($value);

        if (strlen($digits) !== 14) {
            return (string) $value;
        }

        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits) ?: $digits;
    }
}

if (! function_exists('format_cpf')) {
    function format_cpf(?string $value): string
    {
        $digits = normalize_digits($value);

        if (strlen($digits) !== 11) {
            return (string) $value;
        }

        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits) ?: $digits;
    }
}

if (! function_exists('format_cep')) {
    function format_cep(?string $value): string
    {
        $digits = normalize_digits($value);

        if (strlen($digits) !== 8) {
            return (string) $value;
        }

        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $digits) ?: $digits;
    }
}

if (! function_exists('format_phone_br')) {
    function format_phone_br(?string $value): string
    {
        $digits = normalize_digits($value);

        if (str_starts_with($digits, '55') && strlen($digits) > 11) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $digits) ?: $digits;
        }

        if (strlen($digits) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $digits) ?: $digits;
        }

        return (string) $value;
    }
}

if (! function_exists('format_placa')) {
    function format_placa(?string $value): string
    {
        $plate = strtoupper(trim((string) $value));
        $normalized = preg_replace('/[^A-Z0-9]/', '', $plate) ?? '';

        if (strlen($normalized) !== 7) {
            return $plate;
        }

        return substr($normalized, 0, 3).'-'.substr($normalized, 3);
    }
}

if (! function_exists('format_money_br')) {
    function format_money_br(float|int $value, string $currency = 'R$'): string
    {
        return trim($currency.' '.number_format((float) $value, 2, ',', '.'));
    }
}

if (! function_exists('calculate_percentage')) {
    function calculate_percentage(float|int $part, float|int $total, int $precision = 2): float
    {
        if ((float) $total === 0.0) {
            return 0.0;
        }

        return round((((float) $part) / ((float) $total)) * 100, $precision);
    }
}

if (! function_exists('calculate_sucata_credit')) {
    function calculate_sucata_credit(float|int $pesoSucataKg, float|int $valorBaseSucataKg, int $precision = 2): float
    {
        return round(((float) $pesoSucataKg) * ((float) $valorBaseSucataKg), $precision);
    }
}

if (! function_exists('calculate_battery_final_price')) {
    function calculate_battery_final_price(
        float|int $precoUnitario,
        float|int $pesoSucataKg,
        float|int $valorBaseSucataKg,
        bool $devolveuSucata,
        int $precision = 2,
    ): float {
        if ($devolveuSucata) {
            return round((float) $precoUnitario, $precision);
        }

        return round((float) $precoUnitario + calculate_sucata_credit($pesoSucataKg, $valorBaseSucataKg, $precision), $precision);
    }
}
