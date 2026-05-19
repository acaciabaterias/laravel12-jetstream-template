<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformCurrencyManager;
use App\Models\UsuarioPlataforma;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithPlatformCurrencySetup;
use Tests\TestCase;

class PlatformCurrencyPublicationTest extends TestCase
{
    use InteractsWithPlatformCurrencySetup;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_currencies.events.publish_to_backbone', true);

        $this->runPlatformCurrencyMigrations(includeBackbone: true);
    }

    public function test_billing_user_can_publish_a_currency_bundle_and_record_rates(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();
        $this->actingAs($billing, 'platform');

        Livewire::test(PlatformCurrencyManager::class)
            ->set('selectedCurrencies', ['BRL', 'USD', 'EUR'])
            ->set('baseCurrency', 'BRL')
            ->set('defaultCurrency', 'USD')
            ->set('exchangeRates', [
                'USD' => '5.42',
                'EUR' => '5.93',
            ])
            ->call('publishCurrencies')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('platform_currency_publication_records', [
            'status' => 'active',
            'base_currency_code' => 'BRL',
            'default_currency_code' => 'USD',
            'published_by' => $billing->id,
        ], 'central');
        $this->assertDatabaseHas('platform_currency_rate_entries', [
            'currency_code' => 'USD',
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'MOEDAS_PLATAFORMA_PUBLICADAS',
            'origin_context' => 'platform-currencies',
        ], 'central');
    }
}
