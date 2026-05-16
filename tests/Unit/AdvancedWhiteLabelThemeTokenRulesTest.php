<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Operations\AdvancedWhiteLabelTokenValidator;
use Tests\NonDatabaseTestCase;

class AdvancedWhiteLabelThemeTokenRulesTest extends NonDatabaseTestCase
{
    public function test_it_normalizes_valid_theme_tokens(): void
    {
        $result = (new AdvancedWhiteLabelTokenValidator)->validate([
            'primary' => '123B66',
            'secondary' => '#F59E0B',
            'surface' => '#F8FAFC',
            'accent' => '#0F766E',
            'text' => '#0F172A',
        ]);

        $this->assertTrue($result['passed']);
        $this->assertSame('#123B66', $result['normalized_tokens']['primary']);
    }

    public function test_it_rejects_missing_required_tokens(): void
    {
        $result = (new AdvancedWhiteLabelTokenValidator)->validate([
            'primary' => '#123B66',
            'surface' => '#F8FAFC',
        ]);

        $this->assertFalse($result['passed']);
        $this->assertNotEmpty($result['messages']);
    }
}
