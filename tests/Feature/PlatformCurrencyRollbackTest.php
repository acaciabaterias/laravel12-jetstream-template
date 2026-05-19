<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformCurrencyManager;
use App\Models\PlatformCurrencyIssueReport;
use App\Models\PlatformCurrencyPublicationRecord;
use App\Models\UsuarioPlataforma;
use App\Support\Platform\PlatformCurrencyPublicationStatus;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithPlatformCurrencySetup;
use Tests\TestCase;

class PlatformCurrencyRollbackTest extends TestCase
{
    use InteractsWithPlatformCurrencySetup;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_currencies.events.publish_to_backbone', true);

        $this->runPlatformCurrencyMigrations(includeBackbone: true);
    }

    public function test_super_admin_can_rollback_to_the_last_healthy_currency_publication(): void
    {
        $superAdmin = UsuarioPlataforma::factory()->superAdmin()->create();
        $baseline = PlatformCurrencyPublicationRecord::factory()->create([
            'status' => PlatformCurrencyPublicationStatus::Superseded->value,
            'published_at' => now()->subDay(),
        ]);
        $candidate = PlatformCurrencyPublicationRecord::factory()->create([
            'status' => PlatformCurrencyPublicationStatus::Active->value,
            'published_at' => now(),
        ]);
        PlatformCurrencyIssueReport::factory()->create([
            'platform_currency_publication_record_id' => $candidate->id,
            'resolution_status' => 'open',
        ]);

        $this->actingAs($superAdmin, 'platform');

        Livewire::test(PlatformCurrencyManager::class)
            ->set('rollbackReason', 'Tabela inconsistente para a moeda ativa.')
            ->call('rollbackPublication', $candidate->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('platform_currency_publication_records', [
            'id' => $candidate->id,
            'status' => 'rolled_back',
            'rolled_back_by' => $superAdmin->id,
        ], 'central');
        $this->assertDatabaseHas('platform_currency_publication_records', [
            'id' => $baseline->id,
            'status' => 'active',
        ], 'central');
        $this->assertDatabaseHas('platform_currency_issue_reports', [
            'platform_currency_publication_record_id' => $candidate->id,
            'resolution_status' => 'rolled_back',
            'resolved_by' => $superAdmin->id,
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'ROLLBACK_MOEDAS_PLATAFORMA_EXECUTADO',
            'origin_context' => 'platform-currencies',
        ], 'central');
    }
}
