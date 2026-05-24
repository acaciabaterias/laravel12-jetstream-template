<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class DashboardPerformanceStructureTest extends TestCase
{
    private function dashboardViewPath(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources/views/dashboard.blade.php';
    }

    public function test_dashboard_defers_heavy_livewire_components(): void
    {
        $dashboardView = file_get_contents($this->dashboardViewPath());

        $this->assertIsString($dashboardView);

        $lazyComponents = [
            '<livewire:filial-selector lazy />',
            '<livewire:user-manager lazy />',
            '<livewire:estoque-dashboard lazy />',
            '<livewire:estoque-adjustment-form lazy />',
            '<livewire:xml-import-form lazy />',
            '<livewire:conta-sucata-dashboard lazy />',
            '<livewire:vale-form lazy />',
            '<livewire:vale-conversion-actions lazy />',
            '<livewire:ordem-servico-form lazy />',
            '<livewire:vale-list lazy />',
            '<livewire:route-planner lazy />',
            '<livewire:logistics-dashboard lazy />',
            '<livewire:delivery-route-screen lazy />',
            '<livewire:garantia-board lazy />',
            '<livewire:garantia-form lazy />',
            '<livewire:garantia-laudo-form lazy />',
            '<livewire:finance-dashboard lazy />',
            '<livewire:cash-flow-panel lazy />',
            '<livewire:margin-analysis-grid lazy />',
            '<livewire:fiscal-contingency-dashboard lazy />',
            '<livewire:cnab-upload-panel lazy />',
            '<livewire:integration-backbone-dashboard lazy />',
        ];

        foreach ($lazyComponents as $lazyComponent) {
            $this->assertStringContainsString($lazyComponent, $dashboardView);
        }
    }
}
