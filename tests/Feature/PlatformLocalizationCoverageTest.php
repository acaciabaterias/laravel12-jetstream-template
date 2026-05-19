<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UsuarioPlataforma;
use App\Services\Platform\PlatformLocaleInspectionService;
use App\Services\Platform\PlatformLocalePublicationService;
use Tests\Concerns\InteractsWithPlatformLocalizationSetup;
use Tests\TestCase;

class PlatformLocalizationCoverageTest extends TestCase
{
    use InteractsWithPlatformLocalizationSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformLocalizationMigrations();
    }

    public function test_publication_generates_coverage_snapshot_and_missing_key_reports(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);

        $publication = app(PlatformLocalePublicationService::class)->publish(
            ['pt_BR', 'en', 'es'],
            'pt_BR',
            'en',
            $support->id,
        );

        $inspection = app(PlatformLocaleInspectionService::class)->inspect([
            'locale' => 'es',
            'limit' => 10,
            'publication_limit' => 5,
        ]);

        $this->assertSame($publication->id, $inspection['summary']['active_publication_id']);
        $this->assertSame('en', $inspection['summary']['fallback_locale']);
        $this->assertNotEmpty($inspection['coverage']);
        $this->assertSame('es', $inspection['missing_key_reports'][0]->locale_code);
        $this->assertSame('Go to ERP login', $inspection['missing_key_reports'][0]->translation_key);
    }
}
