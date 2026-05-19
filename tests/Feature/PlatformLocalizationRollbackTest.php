<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformLocalizationManager;
use App\Models\PlatformLocaleMissingKeyReport;
use App\Models\PlatformLocalePublicationRecord;
use App\Models\UsuarioPlataforma;
use App\Support\Platform\PlatformLocalePublicationStatus;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithPlatformLocalizationSetup;
use Tests\TestCase;

class PlatformLocalizationRollbackTest extends TestCase
{
    use InteractsWithPlatformLocalizationSetup;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_localization.events.publish_to_backbone', true);

        $this->runPlatformLocalizationMigrations(includeBackbone: true);
    }

    public function test_super_admin_can_rollback_to_the_last_healthy_locale_publication(): void
    {
        $superAdmin = UsuarioPlataforma::factory()->superAdmin()->create();
        $baseline = PlatformLocalePublicationRecord::factory()->create([
            'status' => PlatformLocalePublicationStatus::Superseded->value,
            'published_at' => now()->subDay(),
        ]);
        $candidate = PlatformLocalePublicationRecord::factory()->create([
            'status' => PlatformLocalePublicationStatus::Active->value,
            'published_at' => now(),
        ]);
        PlatformLocaleMissingKeyReport::factory()->create([
            'platform_locale_publication_record_id' => $candidate->id,
            'resolution_status' => 'open',
        ]);

        $this->actingAs($superAdmin, 'platform');

        Livewire::test(PlatformLocalizationManager::class)
            ->set('rollbackReason', 'Coverage regressiva no locale publicado.')
            ->call('rollbackPublication', $candidate->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('platform_locale_publication_records', [
            'id' => $candidate->id,
            'status' => 'rolled_back',
            'rolled_back_by' => $superAdmin->id,
        ], 'central');
        $this->assertDatabaseHas('platform_locale_publication_records', [
            'id' => $baseline->id,
            'status' => 'active',
        ], 'central');
        $this->assertDatabaseHas('platform_locale_missing_key_reports', [
            'platform_locale_publication_record_id' => $candidate->id,
            'resolution_status' => 'rolled_back',
            'resolved_by' => $superAdmin->id,
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'ROLLBACK_LOCALIZACAO_PLATAFORMA_EXECUTADO',
            'origin_context' => 'platform-localization',
        ], 'central');
    }
}
