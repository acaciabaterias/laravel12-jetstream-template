<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformLocalizationManager;
use App\Models\UsuarioPlataforma;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithPlatformLocalizationSetup;
use Tests\TestCase;

class PlatformLocalizationPublicationTest extends TestCase
{
    use InteractsWithPlatformLocalizationSetup;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_localization.events.publish_to_backbone', true);

        $this->runPlatformLocalizationMigrations(includeBackbone: true);
    }

    public function test_support_user_can_publish_a_locale_bundle_and_record_missing_keys(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $this->actingAs($support, 'platform');

        Livewire::test(PlatformLocalizationManager::class)
            ->set('selectedLocales', ['pt_BR', 'en', 'es'])
            ->set('defaultLocale', 'pt_BR')
            ->set('fallbackLocale', 'en')
            ->call('publishLocales')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('platform_locale_publication_records', [
            'status' => 'active',
            'default_locale' => 'pt_BR',
            'fallback_locale' => 'en',
            'published_by' => $support->id,
        ], 'central');
        $this->assertDatabaseHas('platform_locale_missing_key_reports', [
            'locale_code' => 'es',
            'translation_key' => 'Go to ERP login',
            'resolution_status' => 'open',
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'LOCALIZACAO_PLATAFORMA_PUBLICADA',
            'origin_context' => 'platform-localization',
        ], 'central');
    }
}
