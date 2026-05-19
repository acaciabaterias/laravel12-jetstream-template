<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformLocalizationManager;
use App\Models\UsuarioPlataforma;
use App\Services\Platform\PlatformLocalePreferenceService;
use Tests\Concerns\InteractsWithPlatformLocalizationSetup;
use Tests\TestCase;

class PlatformLocalizationPreferenceTest extends TestCase
{
    use InteractsWithPlatformLocalizationSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformLocalizationMigrations();
    }

    public function test_platform_operator_can_persist_preferred_locale_and_render_the_admin_panel_in_english(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create([
            'preferred_locale' => 'en',
        ]);

        $response = $this
            ->actingAs($operator, 'platform')
            ->get(route('admin.localization.index'));

        $response
            ->assertOk()
            ->assertSee('Platform internationalization')
            ->assertSeeLivewire(PlatformLocalizationManager::class);
    }

    public function test_platform_operator_can_save_locale_preference_and_persist_it_in_session(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create([
            'preferred_locale' => 'pt_BR',
        ]);

        $session = app('session.store');
        $session->start();

        app(PlatformLocalePreferenceService::class)->updatePreference($operator, 'es', $session);

        $this->assertDatabaseHas('usuarios_plataforma', [
            'id' => $operator->id,
            'preferred_locale' => 'es',
        ], 'central');
        $this->assertSame('es', $session->get('platform_locale'));
    }
}
