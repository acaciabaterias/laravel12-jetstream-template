<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Platform\PlatformCurrencyCoverageService;
use App\Services\Platform\PlatformCurrencyPublicationRules;
use Tests\TestCase;

class PlatformCurrencyPublicationRulesTest extends TestCase
{
    public function test_it_accepts_a_supported_currency_bundle_with_positive_rates(): void
    {
        $coverageSnapshot = app(PlatformCurrencyCoverageService::class)->snapshot(
            ['BRL', 'USD', 'EUR'],
            ['USD' => '5.42', 'EUR' => '5.93'],
            'BRL',
        );

        $validation = app(PlatformCurrencyPublicationRules::class)->validate(
            ['BRL', 'USD', 'EUR'],
            'BRL',
            'USD',
            ['USD' => '5.42', 'EUR' => '5.93'],
            $coverageSnapshot,
        );

        $this->assertTrue($validation['passed']);
        $this->assertSame([], $validation['messages']);
    }
}
