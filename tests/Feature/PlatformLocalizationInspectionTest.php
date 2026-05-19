<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PlatformLocaleMissingKeyReport;
use App\Models\PlatformLocalePublicationRecord;
use App\Models\UsuarioPlataforma;
use App\Support\Platform\PlatformLocalePublicationStatus;
use Tests\Concerns\InteractsWithPlatformLocalizationSetup;
use Tests\TestCase;

class PlatformLocalizationInspectionTest extends TestCase
{
    use InteractsWithPlatformLocalizationSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformLocalizationMigrations();
    }

    public function test_inspection_endpoint_returns_summary_coverage_and_filtered_missing_keys(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $publication = PlatformLocalePublicationRecord::factory()->create([
            'status' => PlatformLocalePublicationStatus::Active->value,
        ]);
        PlatformLocaleMissingKeyReport::factory()->create([
            'platform_locale_publication_record_id' => $publication->id,
            'locale_code' => 'es',
            'severity' => 'critical',
        ]);

        $response = $this
            ->actingAs($support, 'platform')
            ->getJson(route('admin.localization.inspection', [
                'locale' => 'es',
                'severity' => 'critical',
            ]));

        $response
            ->assertOk()
            ->assertJsonPath('summary.default_locale', $publication->default_locale)
            ->assertJsonPath('missing_key_reports.0.locale_code', 'es')
            ->assertJsonPath('missing_key_reports.0.severity', 'critical');
    }
}
