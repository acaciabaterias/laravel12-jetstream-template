<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Platform\PlatformLocalePublicationRules;
use Tests\TestCase;

class PlatformLocalePublicationRulesTest extends TestCase
{
    public function test_it_rejects_publication_when_fallback_is_not_supported(): void
    {
        $rules = new PlatformLocalePublicationRules;

        $validation = $rules->validate(
            ['pt_BR', 'en'],
            'pt_BR',
            'es',
            [
                'pt_BR' => ['coverage_ratio' => 1],
                'en' => ['coverage_ratio' => 1],
            ],
        );

        $this->assertFalse($validation['passed']);
        $this->assertContains('O fallback precisa estar dentro da lista suportada.', $validation['messages']);
    }
}
