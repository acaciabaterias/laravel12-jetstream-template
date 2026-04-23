<?php

namespace Tests\Feature;

use App\Jobs\SendGuaranteeWhatsAppNotificationJob;
use App\Livewire\GarantiaForm;
use App\Livewire\GarantiaLaudoForm;
use App\Models\Bateria;
use App\Models\BateriaEmprestimo;
use App\Models\Cliente;
use App\Models\NotificacaoWhatsApp;
use App\Models\OrdemServicoGarantia;
use App\Models\User;
use App\Models\Vale;
use Livewire\Livewire;
use Tests\TestCase;

class GuaranteesFeedbackTest extends TestCase
{
    public function test_opening_guarantee_with_loan_battery_generates_term_reference(): void
    {
        $tecnico = User::factory()->withPersonalTeam()->create(['papel' => 'tecnico', 'ativo' => true]);
        $cliente = Cliente::factory()->create();
        $bateriaAnalise = Bateria::create(['sku' => 'GAR-001', 'marca' => 'Moura']);
        $bateriaEmprestimo = Bateria::create(['sku' => 'GAR-LOAN', 'marca' => 'Heliar']);
        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $tecnico->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'created_by' => $tecnico->id,
        ]);

        $this->actingAs($tecnico);

        Livewire::test(GarantiaForm::class)
            ->set('clienteId', $cliente->id)
            ->set('bateriaId', $bateriaAnalise->id)
            ->set('valeOriginalId', $vale->id)
            ->call('openGuarantee')
            ->set('bateriaEmprestimoId', $bateriaEmprestimo->id)
            ->call('checkoutLoanBattery')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ordens_servico_garantia', [
            'cliente_id' => $cliente->id,
            'bateria_id' => $bateriaAnalise->id,
        ]);
        $this->assertDatabaseHas('baterias_emprestimo', [
            'bateria_usada_id' => $bateriaEmprestimo->id,
        ]);
        $this->assertNotNull(BateriaEmprestimo::query()->first()?->termo_arquivo_path);
    }

    public function test_improcedente_report_generates_charge_and_notification(): void
    {
        $tecnico = User::factory()->withPersonalTeam()->create(['papel' => 'tecnico', 'ativo' => true]);
        $cliente = Cliente::factory()->create(['telefone' => '11999999999']);
        $bateria = Bateria::create(['sku' => 'GAR-002', 'marca' => 'Bosch']);
        $garantia = OrdemServicoGarantia::query()->create([
            'cliente_id' => $cliente->id,
            'bateria_id' => $bateria->id,
            'data_abertura' => now(),
            'status' => 'aberta',
        ]);

        $this->actingAs($tecnico);

        Livewire::test(GarantiaLaudoForm::class)
            ->call('loadGuarantee', $garantia->id)
            ->set('laudo', 'Sulfatacao por mau uso')
            ->set('resultado', 'improcedente')
            ->set('cobrancaValor', '180.00')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ordens_servico_garantia', [
            'id' => $garantia->id,
            'resultado' => 'improcedente',
            'status' => 'aguardando_pagamento',
        ]);
        $this->assertDatabaseHas('notificacoes_whatsapp', [
            'os_garantia_id' => $garantia->id,
            'status' => 'enviado',
        ]);
    }

    public function test_whatsapp_failure_does_not_break_guarantee_flow(): void
    {
        $notificacao = NotificacaoWhatsApp::query()->create([
            'os_garantia_id' => OrdemServicoGarantia::query()->create([
                'cliente_id' => Cliente::factory()->create()->id,
                'bateria_id' => Bateria::create(['sku' => 'GAR-003', 'marca' => 'Zetta'])->id,
                'data_abertura' => now(),
                'status' => 'aberta',
            ])->id,
            'cliente_telefone' => null,
            'status' => 'pendente',
            'mensagem' => 'Teste falha',
        ]);

        (new SendGuaranteeWhatsAppNotificationJob($notificacao->id, true))->handle();

        $this->assertDatabaseHas('notificacoes_whatsapp', [
            'id' => $notificacao->id,
            'status' => 'falha',
        ]);
    }

    public function test_return_index_is_updated_after_guarantee_report(): void
    {
        $tecnico = User::factory()->create(['papel' => 'tecnico', 'ativo' => true]);
        $cliente = Cliente::factory()->create(['telefone' => '11888888888']);
        $bateria = Bateria::create(['sku' => 'GAR-004', 'marca' => 'Pioneiro']);
        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $tecnico->id,
            'status' => 'faturado',
            'data_criacao' => now(),
            'data_faturamento' => now(),
            'created_by' => $tecnico->id,
        ]);
        $vale->itens()->create([
            'bateria_id' => $bateria->id,
            'quantidade' => 2,
            'preco_unitario_original' => 100,
            'preco_unitario_final' => 100,
            'flag_devolveu_sucata' => true,
        ]);
        $garantia = OrdemServicoGarantia::query()->create([
            'cliente_id' => $cliente->id,
            'bateria_id' => $bateria->id,
            'data_abertura' => now(),
            'status' => 'aberta',
        ]);

        $this->actingAs($tecnico);

        Livewire::test(GarantiaLaudoForm::class)
            ->call('loadGuarantee', $garantia->id)
            ->set('laudo', 'Analise concluida')
            ->set('resultado', 'procedente')
            ->call('save');

        $this->assertDatabaseHas('indices_retorno_produto', [
            'bateria_id' => $bateria->id,
            'total_vendidas' => 2,
            'total_garantias' => 1,
        ]);
    }

    public function test_dashboard_renders_guarantee_components_for_technical_roles(): void
    {
        $filial = \App\Models\Filial::factory()->create();
        $tecnico = User::factory()->withPersonalTeam()->create([
            'papel' => 'tecnico',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($tecnico);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeLivewire('garantia-board')
            ->assertSeeLivewire('garantia-form')
            ->assertSeeLivewire('garantia-laudo-form');
    }

    public function test_dashboard_hides_guarantee_components_for_non_technical_roles(): void
    {
        $filial = \App\Models\Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'vendedor',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertDontSeeLivewire('garantia-board')
            ->assertDontSeeLivewire('garantia-form')
            ->assertDontSeeLivewire('garantia-laudo-form');
    }
}
