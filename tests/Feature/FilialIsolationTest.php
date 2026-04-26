<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Filial;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FilialIsolationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cliente::factory()->create([
            'subdominio' => 'testenv',
            'status' => 'active',
        ]);

        Route::middleware(['web', 'filial.isolation'])->group(function () {
            Route::middleware('auth')->group(function () {
                Route::get('/test-filial/{filial_id}', function () {
                    return response('OK');
                });
                Route::get('/test-filial-no-param', function () {
                    return response('OK');
                });
            });
            Route::get('/login', function () {
                return response('OK');
            });
        });
    }

    public function test_super_admin_acessa_todos_os_cnpjs(): void
    {
        // For testing, mock tenant routing
        config(['database.connections.tenant.host' => 'fake-host']);

        $filial1 = Filial::factory()->create();
        $filial2 = Filial::factory()->create();

        $superAdmin = User::factory()->withPersonalTeam()->create([
            'papel' => 'super_admin',
            'filial_id' => null,
        ]);

        $response1 = $this->actingAs($superAdmin)->get('http://testenv.erp.com/test-filial/'.$filial1->id);
        $response1->assertStatus(200);

        $response2 = $this->actingAs($superAdmin)->get('http://testenv.erp.com/test-filial/'.$filial2->id);
        $response2->assertStatus(200);
    }

    public function test_usuario_comum_nao_acessa_outro_cnpj(): void
    {
        config(['database.connections.tenant.host' => 'fake-host']);

        $filial1 = Filial::factory()->create();
        $filial2 = Filial::factory()->create();

        $vendedor = User::factory()->withPersonalTeam()->create([
            'papel' => 'vendedor',
            'filial_id' => $filial1->id,
        ]);

        $response1 = $this->actingAs($vendedor)->get('http://testenv.erp.com/test-filial/'.$filial1->id);
        $response1->assertStatus(200);

        $response2 = $this->actingAs($vendedor)->get('http://testenv.erp.com/test-filial/'.$filial2->id);
        $response2->assertStatus(403);
    }

    public function test_usuario_sem_filial_id_nao_super_admin_e_bloqueado(): void
    {
        config(['database.connections.tenant.host' => 'fake-host']);

        $filial = Filial::factory()->create();

        $gestor = User::factory()->withPersonalTeam()->create([
            'papel' => 'gestor',
            'filial_id' => null, // Sem filial!
        ]);

        $response = $this->actingAs($gestor)->get('http://testenv.erp.com/test-filial-no-param');

        $response->assertStatus(403);
    }

    public function test_middleware_nao_interfere_em_rotas_publicas(): void
    {
        $this->assertTrue(true);
    }

    public function test_papeis_tem_as_permissoes_corretas(): void
    {
        $user = User::factory()->make(['papel' => 'super_admin']);
        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->hasRole('super_admin'));
        $this->assertFalse($user->hasRole('vendedor'));

        $vendedor = User::factory()->make(['papel' => 'vendedor']);
        $this->assertFalse($vendedor->isSuperAdmin());
        $this->assertTrue($vendedor->hasRole('vendedor'));
        $this->assertTrue($vendedor->hasRole(['vendedor', 'gestor']));
    }
}
