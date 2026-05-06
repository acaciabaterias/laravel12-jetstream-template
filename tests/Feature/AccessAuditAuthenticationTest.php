<?php

namespace Tests\Feature;

use App\Http\Middleware\PrometheusMetrics;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AccessAuditAuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PrometheusMetrics::class);

        if (! Schema::hasTable('audit_logs_acesso')) {
            Schema::create('audit_logs_acesso', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->string('ip', 45);
                $table->text('user_agent')->nullable();
                $table->boolean('sucesso');
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function test_active_user_can_authenticate_and_access_audit_is_recorded(): void
    {
        $user = User::factory()->create([
            'email' => 'rbac-active@test.local',
            'password' => 'password',
            'ativo' => true,
        ]);

        $response = $this->withHeader('User-Agent', 'FeatureTestAgent/1.0')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('audit_logs_acesso', [
            'user_id' => $user->id,
            'sucesso' => true,
            'ip' => '127.0.0.1',
            'user_agent' => 'FeatureTestAgent/1.0',
        ]);
    }

    public function test_inactive_user_cannot_authenticate_and_failure_is_audited(): void
    {
        $user = User::factory()->create([
            'email' => 'rbac-inactive@test.local',
            'password' => 'password',
            'ativo' => false,
        ]);

        $response = $this->withHeader('User-Agent', 'FeatureTestAgent/1.0')
            ->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs_acesso', [
            'user_id' => $user->id,
            'sucesso' => false,
            'ip' => '127.0.0.1',
            'user_agent' => 'FeatureTestAgent/1.0',
        ]);
    }
}
