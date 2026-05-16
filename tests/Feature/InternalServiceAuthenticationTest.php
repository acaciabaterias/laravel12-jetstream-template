<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class InternalServiceAuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.internal_key', 'test-secret-key-123');
    }

    public function test_it_rejects_requests_without_internal_key(): void
    {
        $response = $this->postJson('/api/internal/webhooks/fiscal/status');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthorized service request.']);
    }

    public function test_it_rejects_requests_with_invalid_internal_key(): void
    {
        $response = $this->postJson('/api/internal/webhooks/fiscal/status', [], [
            'X-Internal-Service-Key' => 'wrong-key-456'
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthorized service request.']);
    }

    public function test_it_accepts_requests_with_valid_internal_key(): void
    {
        $response = $this->postJson('/api/internal/webhooks/fiscal/status', [], [
            'X-Internal-Service-Key' => 'test-secret-key-123'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'received']);
    }

    public function test_it_returns_500_if_server_key_is_not_configured(): void
    {
        Config::set('services.internal_key', null);

        $response = $this->postJson('/api/internal/webhooks/fiscal/status', [], [
            'X-Internal-Service-Key' => 'test-secret-key-123'
        ]);

        $response->assertStatus(500)
                 ->assertJson(['message' => 'Configuração de autenticação interna ausente.']);
    }
}
