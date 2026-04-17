<?php

namespace Tests\Feature;

use App\Jobs\ImportNFeJob;
use App\Models\Bateria;
use App\Models\Deposito;
use App\Models\EstoqueMovimentacao;
use App\Models\EstoqueSaldo;
use App\Models\Filial;
use App\Models\Fornecedor;
use App\Models\User;
use App\Services\NFeParserService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventoryAndLogisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_nfe_parser_decodes_essential_sefaz_xml_fields()
    {
        $dummyXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<nfeProc versao="4.00" xmlns="http://www.portalfiscal.inf.br/nfe">
    <NFe>
        <infNFe Id="NFe123456789" versao="4.00">
            <ide>
                <dhEmi>2026-04-17T10:00:00-03:00</dhEmi>
            </ide>
            <emit>
                <CNPJ>12345678000199</CNPJ>
                <xNome>Fabricante Ficticio S.A.</xNome>
            </emit>
            <det nItem="1">
                <prod>
                    <cProd>BAT01</cProd>
                    <cEAN>1234567890123</cEAN>
                    <xProd>Bateria Teste</xProd>
                    <NCM>85071090</NCM>
                    <qCom>10.00</qCom>
                    <vUnCom>150.50</vUnCom>
                </prod>
            </det>
        </infNFe>
    </NFe>
</nfeProc>
XML;

        $parser = new NFeParserService();
        $parsed = $parser->parse($dummyXml);

        $this->assertEquals('123456789', $parsed['chave']);
        $this->assertEquals('Fabricante Ficticio S.A.', $parsed['fornecedor']['nome']);
        $this->assertEquals('12345678000199', $parsed['fornecedor']['cnpj']);
        
        $this->assertCount(1, $parsed['itens']);
        $this->assertEquals('BAT01', $parsed['itens'][0]['codigo_fornecedor']);
        $this->assertEquals(10, $parsed['itens'][0]['quantidade']);
        $this->assertEquals(150.50, $parsed['itens'][0]['valor_unitario']);
    }

    public function test_estoque_movimentacao_observer_strictly_prevents_negative_stock()
    {
        $filial = Filial::factory()->create();
        $deposito = Deposito::create(['nome' => 'Principal', 'filial_id' => $filial->id]);
        $bateria = Bateria::create(['sku' => 'B001', 'marca' => 'BateriaA', 'filial_id' => $filial->id]);

        $user = User::factory()->create(['filial_id' => $filial->id]);
        $this->actingAs($user);

        // Valid Entrada
        EstoqueMovimentacao::create([
            'bateria_id' => $bateria->id,
            'filial_id' => $filial->id,
            'deposito_id' => $deposito->id,
            'tipo' => 'entrada',
            'quantidade' => 5,
            'origem' => 'Ajuste',
        ]);

        $this->assertDatabaseHas('estoque_saldos', [
            'bateria_id' => $bateria->id,
            'quantidade_atual' => 5,
        ]);

        // Invalid Saída -> Expect Exception from DB lock / observer Logic
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Estoque insuficiente/');

        EstoqueMovimentacao::create([
            'bateria_id' => $bateria->id,
            'filial_id' => $filial->id,
            'deposito_id' => $deposito->id,
            'tipo' => 'saida',
            'quantidade' => 10,
            'origem' => 'Ajuste',
        ]);
    }

    public function test_import_nfe_job_generates_stock_and_increments_sucata_debits()
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->create(['filial_id' => $filial->id]);
        $deposito = Deposito::create(['nome' => 'Loja NFe', 'filial_id' => $filial->id]);
        
        // Product WITH SCRAP TRACKING active
        $bateria = Bateria::create([
            'sku' => 'BAT-REVERSE',
            'marca' => 'Eco',
            'filial_id' => $filial->id,
            'tem_logistica_reversa' => true,
            'peso_sucata_kg' => 15.5,
            'valor_base_sucata_kg' => 4.00,
        ]);

        $fornecedorData = ['nome' => 'Supplier', 'cnpj' => '8888'];
        $mappedItems = [
            [
                'bateria_id' => $bateria->id,
                'quantidade' => 10,
            ]
        ];

        $job = new ImportNFeJob($filial->id, $deposito->id, $user->id, 'key123', $fornecedorData, $mappedItems);
        $job->handle();

        $this->assertDatabaseHas('estoque_saldos', [
            'bateria_id' => $bateria->id,
            'quantidade_atual' => 10,
        ]);

        // Logic check for Scrap (10 batteries * 15.5kg = 155kg debit)
        // 155kg * 4.00 = 620 financeiro
        $fornecedor = Fornecedor::where('cnpj', '8888')->first();
        
        $this->assertEquals(-155.00, $fornecedor->saldo_sucata_kg);
        $this->assertEquals(-620.00, $fornecedor->saldo_sucata_financeiro);
    }
}
