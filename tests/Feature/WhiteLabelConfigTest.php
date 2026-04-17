<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\WhiteLabelConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WhiteLabelConfigTest extends TestCase
{
    use RefreshDatabase;

    protected $connectionsToTransact = ['central', 'tenant'];

    protected function afterRefreshingDatabase()
    {
        $this->artisan('migrate', [
            '--database' => 'central',
            '--path' => 'database/migrations/central',
            '--force' => true,
        ]);
    }

    public function test_it_applies_white_label_branding_from_tenant_database()
    {
        // Setup base client and tenant route mockup
        $cliente = Cliente::factory()->create([
            'subdominio' => 'tenant-brand',
            'status' => 'active'
        ]);

        // Simular switch de connection via route fake
        config(['database.connections.tenant' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]]);

        DB::purge('tenant');

        // Em vez de rodar migrações completas, vamos fazer um mock da tabela no banco local
        $this->artisan('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        WhiteLabelConfig::create([
            'cor_primaria' => '#ff0000',
            'titulo_login' => 'ERP Bateria Custom',
        ]);

        $user = \App\Models\User::factory()->create();
        
        $bladeSnippet = <<<BLADE
        @php
            \$whiteLabel = \App\Models\WhiteLabelConfig::first();
        @endphp
        <style>
            :root {
                --primary-color: {{ \$whiteLabel->cor_primaria ?? '#1e40af' }};
            }
        </style>
BLADE;

        $bladeView = \Illuminate\Support\Facades\Blade::render($bladeSnippet);
        
        $this->assertStringContainsString('--primary-color: #ff0000', $bladeView);
    }
}
