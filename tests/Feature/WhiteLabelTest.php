<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class WhiteLabelTest extends TestCase
{
    public function test_white_label_branding_is_applied_to_application_layout(): void
    {
        $subdomain = fake()->unique()->slug(1);
        $cliente = \App\Models\Cliente::factory()->create([
            'subdominio' => $subdomain,
            'status' => 'active',
        ]);

        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'super_admin',
            'filial_id' => null,
        ]);

        \App\Models\WhiteLabelConfig::create([
            'titulo_login' => 'ERP Personalizado',
            'cor_primaria' => '#ff0000',
            'cor_secundaria' => '#00ff00',
            'cor_fundo' => '#0000ff',
            'logo_url' => 'https://example.com/logo.png',
            'favicon_url' => 'https://example.com/favicon.png',
        ]);

        $response = $this->actingAs($user)->get("http://{$subdomain}.erp.com/dashboard");

        $response->assertStatus(200);
        $response->assertSee('ERP Personalizado');
        $response->assertSee('#ff0000');
        $response->assertSee('#00ff00');
        $response->assertSee('#0000ff');
        $response->assertSee('https://example.com/logo.png');
        $response->assertSee('https://example.com/favicon.png');
    }
}
