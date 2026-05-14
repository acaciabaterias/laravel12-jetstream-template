<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Operations\AdvancedWhiteLabelTokenValidator;
use Tests\NonDatabaseTestCase;

class AdvancedWhiteLabelPublicationRulesTest extends NonDatabaseTestCase
{
    public function test_it_blocks_theme_publication_when_contrast_is_insufficient(): void
    {
        $result = (new AdvancedWhiteLabelTokenValidator)->validate([
            'primary' => '#FFFFFF',
            'secondary' => '#EEEEEE',
            'surface' => '#FFFFFF',
            'accent' => '#DDDDDD',
            'text' => '#F8FAFC',
        ]);

        $this->assertFalse($result['passed']);
        $this->assertStringContainsString('Contraste insuficiente', $result['messages'][0]);
    }
}
