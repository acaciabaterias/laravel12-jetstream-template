<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PlatformCurrencyPublicationRecord;
use App\Models\UsuarioPlataforma;
use App\Support\Platform\PlatformCurrencyPublicationStatus;
use Tests\Concerns\InteractsWithPlatformCurrencySetup;
use Tests\TestCase;

class PlatformCurrencyFallbackTest extends TestCase
{
    use InteractsWithPlatformCurrencySetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformCurrencyMigrations();
    }

    public function test_invalid_preferred_currency_falls_back_to_the_active_default_currency(): void
    {
        PlatformCurrencyPublicationRecord::factory()->create([
            'status' => PlatformCurrencyPublicationStatus::Active->value,
            'default_currency_code' => 'USD',
            'published_at' => now(),
        ]);

        $operator = UsuarioPlataforma::factory()->billing()->create([
            'preferred_currency' => 'JPY',
        ]);

        $response = $this
            ->actingAs($operator, 'platform')
            ->get(route('admin.currencies.index'));

        $response
            ->assertOk()
            ->assertSee('Resolved currency')
            ->assertSee('USD')
            ->assertSee('$ 5.756,46');
    }
}
