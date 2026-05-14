<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\TenantThemeVersion;
use App\Services\Operations\AdvancedWhiteLabelEventPublisher;
use App\Services\Operations\AdvancedWhiteLabelPublicationService;
use App\Services\Operations\AdvancedWhiteLabelRollbackService;
use App\Services\Operations\AdvancedWhiteLabelTokenValidator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Tests\NonDatabaseTestCase;

class AdvancedWhiteLabelRollbackRulesTest extends NonDatabaseTestCase
{
    public function test_it_selects_the_latest_published_theme_other_than_the_current_one(): void
    {
        $rollbackService = new AdvancedWhiteLabelRollbackService(
            new AdvancedWhiteLabelPublicationService(
                new AdvancedWhiteLabelTokenValidator,
                $this->createMock(AdvancedWhiteLabelEventPublisher::class)
            ),
            $this->createMock(AdvancedWhiteLabelEventPublisher::class)
        );

        $first = new TenantThemeVersion(['version_label' => 'v1']);
        $first->id = 10;
        $first->published_at = CarbonImmutable::now()->subDay();

        $second = new TenantThemeVersion(['version_label' => 'v2']);
        $second->id = 11;
        $second->published_at = CarbonImmutable::now();

        $restored = $rollbackService->findRestoredThemeVersion(new Collection([$first, $second]), 11);

        $this->assertSame(10, $restored?->id);
    }
}
