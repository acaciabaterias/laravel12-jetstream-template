<?php

namespace Tests\Unit;

use App\Models\CertificadoDigital;
use App\Models\Cliente;
use App\Services\CertificadoDigitalRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CertificadoDigitalRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate', [
            '--database' => 'central',
            '--path' => 'database/migrations/central/2026_05_06_114540_create_certificados_digitais_table.php',
            '--force' => true,
        ]);
    }

    public function test_it_returns_the_highest_priority_active_certificate_for_a_purpose(): void
    {
        $cliente = Cliente::factory()->create();

        CertificadoDigital::factory()->create([
            'cliente_id' => $cliente->id,
            'finalidade' => 'fiscal',
            'status' => 'active',
            'prioridade' => 5,
        ]);

        $esperado = CertificadoDigital::factory()->create([
            'cliente_id' => $cliente->id,
            'finalidade' => 'fiscal',
            'status' => 'active',
            'prioridade' => 30,
        ]);

        CertificadoDigital::factory()->create([
            'cliente_id' => $cliente->id,
            'finalidade' => 'bancario',
            'status' => 'active',
            'prioridade' => 99,
        ]);

        $repositorio = new CertificadoDigitalRepository;
        $resultado = $repositorio->obterAtivoPorFinalidade($cliente->id, 'fiscal');

        $this->assertNotNull($resultado);
        $this->assertSame($esperado->id, $resultado->id);
    }

    public function test_it_ignores_expired_or_revoked_certificates(): void
    {
        $cliente = Cliente::factory()->create();

        CertificadoDigital::factory()->create([
            'cliente_id' => $cliente->id,
            'finalidade' => 'fiscal',
            'status' => 'expired',
            'prioridade' => 40,
        ]);

        CertificadoDigital::factory()->create([
            'cliente_id' => $cliente->id,
            'finalidade' => 'fiscal',
            'status' => 'active',
            'revoked_at' => now(),
            'prioridade' => 45,
        ]);

        $valido = CertificadoDigital::factory()->create([
            'cliente_id' => $cliente->id,
            'finalidade' => 'fiscal',
            'status' => 'active',
            'prioridade' => 15,
            'validade_fim' => now()->addMonth()->toDateString(),
        ]);

        $repositorio = new CertificadoDigitalRepository;
        $resultado = $repositorio->obterAtivoPorFinalidade($cliente->id, 'fiscal');

        $this->assertNotNull($resultado);
        $this->assertSame($valido->id, $resultado->id);
    }
}
