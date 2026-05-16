<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\ExecutiveReportingDashboard;
use App\Models\UsuarioPlataforma;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithExecutiveReportingFixtures;
use Tests\TestCase;

class ExecutiveReportingDrilldownTest extends TestCase
{
    use InteractsWithExecutiveReportingFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runExecutiveReportingMigrations();
        $this->seedExecutiveReportingScenario();
    }

    public function test_executive_dashboard_filters_drilldown_by_plan(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();
        $this->actingAs($operator, 'platform');

        Livewire::test(ExecutiveReportingDashboard::class)
            ->set('planFilter', 'enterprise')
            ->assertSee('Plano Enterprise')
            ->assertSee('MRR R$ 899,90')
            ->assertDontSee('MRR R$ 299,90');
    }
}
