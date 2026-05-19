<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Platform\PlatformCurrencyResolutionRules;
use Tests\TestCase;

class PlatformCurrencyResolutionRulesTest extends TestCase
{
    public function test_it_prefers_the_operator_currency_when_it_is_supported(): void
    {
        $resolvedCurrency = app(PlatformCurrencyResolutionRules::class)->resolve(
            'EUR',
            ['BRL', 'USD', 'EUR'],
            'BRL',
            'BRL',
        );

        $this->assertSame('EUR', $resolvedCurrency);
    }

    public function test_it_falls_back_to_default_currency_when_preference_is_invalid(): void
    {
        $resolvedCurrency = app(PlatformCurrencyResolutionRules::class)->resolve(
            'JPY',
            ['BRL', 'USD', 'EUR'],
            'USD',
            'BRL',
        );

        $this->assertSame('USD', $resolvedCurrency);
    }
}
