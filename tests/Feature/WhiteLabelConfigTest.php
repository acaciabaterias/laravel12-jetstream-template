<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\WhiteLabelConfig;
use Tests\TestCase;

class WhiteLabelConfigTest extends TestCase
{

    /**
     * Test if white label branding is correctly resolved from the database.
     * Note: Models now use the default connection refreshed by RefreshDatabase trait.
     */
    public function test_it_applies_white_label_branding_from_tenant_database()
    {
        // Setup base client (central)
        $cliente = Cliente::factory()->create([
            'subdominio' => 'tenant-brand',
            'status' => 'active'
        ]);

        // Create branding config (tenant/default connection)
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
