<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Platform\PlatformLocaleResolutionRules;
use Tests\TestCase;

class PlatformLocaleResolutionRulesTest extends TestCase
{
    public function test_it_prefers_the_user_locale_when_supported(): void
    {
        $rules = new PlatformLocaleResolutionRules;

        $resolved = $rules->resolve('en', ['pt_BR', 'en'], 'pt_BR', 'en');

        $this->assertSame('en', $resolved);
    }

    public function test_it_falls_back_to_default_then_fallback_when_user_locale_is_invalid(): void
    {
        $rules = new PlatformLocaleResolutionRules;

        $resolved = $rules->resolve('es', ['pt_BR', 'en'], 'pt_BR', 'en');

        $this->assertSame('pt_BR', $resolved);
    }
}
