<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PlatformCurrencyPublicationRecord;
use App\Models\UsuarioPlataforma;
use App\Services\Platform\PlatformCurrencyPreferenceService;
use App\Support\Platform\PlatformCurrencyPublicationStatus;
use Tests\Concerns\InteractsWithPlatformCurrencySetup;
use Tests\TestCase;

class PlatformCurrencyPreferenceTest extends TestCase
{
    use InteractsWithPlatformCurrencySetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformCurrencyMigrations();
    }

    public function test_platform_operator_can_persist_preferred_currency_and_render_the_currency_panel_in_usd(): void
    {
        PlatformCurrencyPublicationRecord::factory()->create([
            'status' => PlatformCurrencyPublicationStatus::Active->value,
            'published_at' => now(),
        ]);

        $operator = UsuarioPlataforma::factory()->billing()->create([
            'preferred_currency' => 'USD',
        ]);

        $response = $this
            ->actingAs($operator, 'platform')
            ->get(route('admin.currencies.index'));

        $response
            ->assertOk()
            ->assertSee('Platform currencies')
            ->assertSee('$ 5.756,46')
            ->assertSee('USD');
    }

    public function test_platform_operator_can_save_currency_preference_and_persist_it_in_session(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create([
            'preferred_currency' => 'BRL',
        ]);
        $session = app('session.store');
        $session->start();

        app(PlatformCurrencyPreferenceService::class)->updatePreference($operator, 'EUR', $session);

        $this->assertDatabaseHas('usuarios_plataforma', [
            'id' => $operator->id,
            'preferred_currency' => 'EUR',
        ], 'central');
        $this->assertSame('EUR', $session->get('platform_currency'));
    }
}
