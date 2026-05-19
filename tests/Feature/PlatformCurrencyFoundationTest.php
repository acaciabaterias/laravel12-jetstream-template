<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PlatformCurrencyCatalogEntry;
use App\Models\PlatformCurrencyIssueReport;
use App\Models\PlatformCurrencyPublicationRecord;
use App\Models\PlatformCurrencyRateEntry;
use App\Models\UsuarioPlataforma;
use App\Services\Platform\PlatformCurrencyEventPublisher;
use App\Support\Platform\PlatformCurrencyIssueResolutionStatus;
use App\Support\Platform\PlatformCurrencyIssueSeverity;
use App\Support\Platform\PlatformCurrencyPublicationStatus;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\InteractsWithPlatformCurrencySetup;
use Tests\TestCase;

class PlatformCurrencyFoundationTest extends TestCase
{
    use InteractsWithPlatformCurrencySetup;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_currencies.events.publish_to_backbone', true);

        $this->runPlatformCurrencyMigrations(includeBackbone: true);
    }

    public function test_platform_currency_tables_are_available(): void
    {
        $this->assertTrue(Schema::connection('central')->hasTable('platform_currency_catalog_entries'));
        $this->assertTrue(Schema::connection('central')->hasTable('platform_currency_publication_records'));
        $this->assertTrue(Schema::connection('central')->hasTable('platform_currency_rate_entries'));
        $this->assertTrue(Schema::connection('central')->hasTable('platform_currency_issue_reports'));
    }

    public function test_platform_currency_models_persist_relationships_and_enum_casts(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();
        $catalog = PlatformCurrencyCatalogEntry::factory()->create([
            'currency_code' => 'USD',
        ]);
        $publication = PlatformCurrencyPublicationRecord::factory()->create([
            'status' => PlatformCurrencyPublicationStatus::Active->value,
            'published_by' => $operator->id,
        ]);
        $rateEntry = PlatformCurrencyRateEntry::factory()->create([
            'platform_currency_publication_record_id' => $publication->id,
            'currency_code' => $catalog->currency_code,
        ]);
        $issueReport = PlatformCurrencyIssueReport::factory()->create([
            'platform_currency_publication_record_id' => $publication->id,
            'resolved_by' => $operator->id,
            'severity' => PlatformCurrencyIssueSeverity::Critical->value,
            'resolution_status' => PlatformCurrencyIssueResolutionStatus::Resolved->value,
        ]);

        $this->assertSame('central', $publication->getConnectionName());
        $this->assertSame($publication->id, $rateEntry->publication->id);
        $this->assertSame($publication->id, $issueReport->publication->id);
        $this->assertSame($operator->id, $publication->publisher->id);
        $this->assertSame(PlatformCurrencyPublicationStatus::Active, $publication->status);
        $this->assertSame(PlatformCurrencyIssueSeverity::Critical, $issueReport->severity);
        $this->assertSame(PlatformCurrencyIssueResolutionStatus::Resolved, $issueReport->resolution_status);
    }

    public function test_platform_currency_permissions_are_restricted_to_billing_roles(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();
        $support = UsuarioPlataforma::factory()->create();

        $this->assertTrue(Gate::forUser($billing)->allows('manage-platform-currencies'));
        $this->assertFalse(Gate::forUser($support)->allows('manage-platform-currencies'));
    }

    public function test_platform_currency_event_publisher_creates_contract_and_central_outbox_record(): void
    {
        Queue::fake();

        app(PlatformCurrencyEventPublisher::class)->publish(
            'MOEDAS_PLATAFORMA_PUBLICADAS',
            [
                'publication_id' => 1,
                'release_key' => 'fx-2026-05-18-100000',
                'base_currency_code' => 'BRL',
                'default_currency_code' => 'USD',
                'supported_currencies' => ['BRL', 'USD', 'EUR'],
                'status' => 'active',
            ],
            ['platform', 'billing', 'analytics'],
        );

        $this->assertDatabaseHas('contratos_evento', [
            'event_type' => 'MOEDAS_PLATAFORMA_PUBLICADAS',
            'producer' => 'platform-currencies',
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'MOEDAS_PLATAFORMA_PUBLICADAS',
            'tenant_external_ref' => 'platform-central',
            'origin_context' => 'platform-currencies',
        ], 'central');
    }
}
