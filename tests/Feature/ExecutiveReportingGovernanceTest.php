<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\ExecutiveReportingDashboard;
use App\Models\ExecutiveReportExport;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\ExecutiveReportingEventPublisher;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery;
use Tests\Concerns\InteractsWithExecutiveReportingFixtures;
use Tests\TestCase;

class ExecutiveReportingGovernanceTest extends TestCase
{
    use InteractsWithExecutiveReportingFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->runExecutiveReportingMigrations();
        $this->seedExecutiveReportingScenario();
    }

    public function test_it_publishes_the_material_event_and_persists_the_export_audit_trail(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();
        $this->actingAs($operator, 'platform');

        $publisher = Mockery::mock(ExecutiveReportingEventPublisher::class);
        $publisher
            ->shouldReceive('publish')
            ->once()
            ->withArgs(function (
                string $eventType,
                ExecutiveReportExport $executiveReportExport,
                array $payload,
                array $consumers,
            ) use ($operator): bool {
                return $eventType === 'RELATORIO_EXECUTIVO_GERADO'
                    && $executiveReportExport->format->value === 'excel'
                    && $payload['report_slug'] === 'executive-overview'
                    && $payload['format'] === 'excel'
                    && $payload['operator'] === $operator->id
                    && $payload['status'] === 'completed'
                    && $payload['filters']['days'] === 30
                    && $consumers === ['backbone', 'observability', 'analytics'];
            });

        $this->app->instance(ExecutiveReportingEventPublisher::class, $publisher);

        Livewire::test(ExecutiveReportingDashboard::class)
            ->call('exportExcel');

        $export = ExecutiveReportExport::query()
            ->with('executionLogs')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('completed', $export->export_status->value);
        $this->assertEquals([
            'days' => 30,
            'period_start' => now()->subDays(29)->toDateString(),
            'period_end' => now()->toDateString(),
            'plan' => 'all',
            'channel' => 'all',
            'portfolio' => 'all',
            'recovery_status' => 'all',
        ], $export->metadata['filters'] ?? []);
        $this->assertSame(['requested', 'completed'], $export->executionLogs->pluck('event_type')->all());
        $this->assertSame($operator->name, $export->executionLogs->last()?->operator_name);
        $this->assertSame('completed', $export->executionLogs->last()?->event_payload['status']);
    }
}
