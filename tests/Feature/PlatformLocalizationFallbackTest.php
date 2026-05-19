<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PlatformLocalePublicationRecord;
use App\Models\UsuarioPlataforma;
use App\Support\Platform\PlatformLocalePublicationStatus;
use Tests\Concerns\InteractsWithPlatformLocalizationSetup;
use Tests\TestCase;

class PlatformLocalizationFallbackTest extends TestCase
{
    use InteractsWithPlatformLocalizationSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformLocalizationMigrations();
    }

    public function test_invalid_preference_falls_back_to_the_active_default_locale(): void
    {
        PlatformLocalePublicationRecord::factory()->create([
            'status' => PlatformLocalePublicationStatus::Active->value,
            'default_locale' => 'pt_BR',
            'fallback_locale' => 'en',
            'supported_locales' => ['pt_BR', 'en'],
        ]);
        $operator = UsuarioPlataforma::factory()->billing()->create([
            'preferred_locale' => 'es',
        ]);

        $response = $this
            ->actingAs($operator, 'platform')
            ->get(route('admin.localization.index'));

        $response
            ->assertOk()
            ->assertSee('Internacionalização da plataforma');
    }
}
