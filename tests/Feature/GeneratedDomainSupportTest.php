<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Casts\JsonChipreCast;
use App\Events\ValeCriado;
use App\Http\Requests\UserRequest;
use App\Http\Requests\ValeRequest;
use App\Jobs\EnviarNotificacaoJob;
use App\Listeners\ReservarEstoqueListener;
use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\Deposito;
use App\Models\EstoqueSaldo;
use App\Models\ItemVale;
use App\Models\User;
use App\Models\Vale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GeneratedDomainSupportTest extends TestCase
{
    public function test_vale_request_requires_at_least_one_item(): void
    {
        $user = User::factory()->create(['papel' => 'vendedor', 'ativo' => true, 'filial_id' => 1]);
        $request = new ValeRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'cliente_id' => Cliente::factory()->create()->id,
            'itens' => [],
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('itens', $validator->errors()->toArray());
    }

    public function test_user_request_blocks_non_super_admin_from_creating_dono(): void
    {
        $manager = User::factory()->create(['papel' => 'gestor', 'ativo' => true, 'filial_id' => 1]);
        $request = new UserRequest;
        $request->setUserResolver(fn () => $manager);
        $request->merge([
            'name' => 'Novo Dono',
            'email' => 'dono@example.com',
            'password' => 'password123',
            'papel' => 'dono',
        ]);

        $this->assertFalse($request->authorize());
    }

    public function test_vale_factory_creates_items_after_creating(): void
    {
        $vale = Vale::factory()->create();

        $this->assertGreaterThanOrEqual(1, $vale->itens()->count());
    }

    public function test_reservar_estoque_listener_reserves_items_for_new_vale(): void
    {
        $user = User::factory()->create(['papel' => 'vendedor', 'ativo' => true, 'filial_id' => 1]);
        $cliente = Cliente::factory()->create();
        $bateria = Bateria::query()->create([
            'sku' => 'GEN-RES-01',
            'marca' => 'Moura',
            'preco_venda' => 400,
            'peso_sucata_kg' => 5,
            'valor_base_sucata_kg' => 2,
        ]);
        $deposito = Deposito::query()->create([
            'nome' => 'Principal',
            'tipo' => 'principal',
            'status' => 'ativo',
        ]);
        EstoqueSaldo::query()->create([
            'bateria_id' => $bateria->id,
            'deposito_id' => $deposito->id,
            'quantidade_atual' => 10,
        ]);

        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'created_by' => $user->id,
        ]);

        ItemVale::query()->create([
            'vale_id' => $vale->id,
            'bateria_id' => $bateria->id,
            'quantidade' => 2,
            'preco_unitario_original' => 400,
            'preco_unitario_final' => 400,
            'flag_devolveu_sucata' => true,
        ]);

        $listener = app(ReservarEstoqueListener::class);
        $listener->handle(new ValeCriado($vale->fresh('itens', 'createdBy')));

        $this->assertDatabaseHas('reservas_estoque', [
            'vale_id' => $vale->id,
            'status' => 'reservada',
        ]);
    }

    public function test_enviar_notificacao_job_uses_whatsapp_microservice(): void
    {
        Http::fake();

        (new EnviarNotificacaoJob('5511999999999', 'whatsapp', [
            'cliente_id' => 1,
            'mensagem' => 'Teste de envio',
        ]))->handle();

        Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/notificacao/enviar'));
    }

    public function test_json_chipre_cast_encrypts_and_decrypts_payload(): void
    {
        $cast = new JsonChipreCast;
        $model = new class extends \Illuminate\Database\Eloquent\Model {};
        $payload = ['foo' => 'bar', 'count' => 2];

        $encrypted = $cast->set($model, 'payload', $payload, []);

        $this->assertIsString($encrypted);
        $this->assertSame($payload, $cast->get($model, 'payload', $encrypted, []));
    }
}
