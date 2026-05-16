<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class StrMacrosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Str::macro('cnpj', fn (?string $value): string => format_cnpj($value));
        Str::macro('cpf', fn (?string $value): string => format_cpf($value));
        Str::macro('telefone', fn (?string $value): string => format_phone_br($value));
        Str::macro('cep', fn (?string $value): string => format_cep($value));
        Str::macro('placa', fn (?string $value): string => format_placa($value));
    }
}
