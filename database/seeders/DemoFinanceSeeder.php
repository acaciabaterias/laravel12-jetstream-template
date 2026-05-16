<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BoletoOrquestrado;
use App\Models\CnabRemessa;
use App\Models\CnabRetornoUpload;
use App\Models\ContaBancaria;
use App\Models\FilaContingencia;
use App\Models\NotaFiscalOrquestrada;
use App\Models\TransacaoFinanceira;
use App\Models\Vale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoFinanceSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('contas_bancarias')) {
            return;
        }

        $conta = ContaBancaria::query()->firstOrCreate(
            ['conta' => '12345-6'],
            [
                'banco' => 'Banco Demo',
                'agencia' => '0001',
                'tipo' => 'corrente',
                'status' => 'ativa',
            ],
        );

        if (Schema::hasTable('transacoes_financeiras')) {
            TransacaoFinanceira::query()->firstOrCreate(
                ['identificador_externo' => 'demo-tx-001'],
                [
                    'conta_bancaria_id' => $conta->id,
                    'tipo' => 'receita',
                    'valor' => 1250,
                    'data_transacao' => now(),
                    'data_vencimento' => now()->addWeek()->toDateString(),
                    'status' => 'pendente',
                    'status_conciliado' => false,
                    'descricao' => 'Recebimento demonstrativo',
                ],
            );
        }

        $vale = Schema::hasTable('vales') ? Vale::query()->first() : null;

        if ($vale instanceof Vale && Schema::hasTable('notas_fiscais_orquestradas')) {
            NotaFiscalOrquestrada::query()->firstOrCreate(
                ['vale_id' => $vale->id],
                [
                    'chave_acesso' => 'NFEDEMO00000001',
                    'xml_path' => 'generated://demo/nfe.xml',
                    'status' => 'emitida',
                    'ms_requisicao_id' => 'demo-fiscal-001',
                    'idempotency_key' => 'demo-fiscal-key-001',
                ],
            );
        }

        if ($vale instanceof Vale && Schema::hasTable('boletos_orquestrados')) {
            BoletoOrquestrado::query()->firstOrCreate(
                ['vale_id' => $vale->id],
                [
                    'nosso_numero' => 'NNDEMO0001',
                    'linha_digitavel' => '34191.79001 01043.510047 91020.150008 5 12340000025000',
                    'pdf_url' => 'https://bank.local/demo-boleto.pdf',
                    'status' => 'emitido',
                    'identificador_externo' => 'demo-boleto-001',
                    'idempotency_key' => 'demo-boleto-key-001',
                ],
            );
        }

        if (Schema::hasTable('cnab_remessas')) {
            $remessa = CnabRemessa::query()->firstOrCreate(
                ['nome_arquivo' => 'demo-remessa.rem'],
                [
                    'tipo_arquivo' => 'remessa',
                    'status' => 'gerada',
                    'arquivo_path' => 'storage/demo-remessa.rem',
                ],
            );

            if (Schema::hasTable('cnab_retorno_uploads')) {
                CnabRetornoUpload::query()->firstOrCreate(
                    ['nome_arquivo' => 'demo-retorno.ret'],
                    [
                        'cnab_remessa_id' => $remessa->id,
                        'status_processamento' => 'processado',
                        'log_processamento' => 'Arquivo demonstrativo processado com sucesso.',
                    ],
                );
            }
        }

        if (Schema::hasTable('filas_contingencia')) {
            FilaContingencia::query()->firstOrCreate(
                ['idempotency_key' => 'demo-contingencia-001'],
                [
                    'tipo_integracao' => 'fiscal',
                    'payload' => ['evento' => 'demo'],
                    'tentativas' => 1,
                    'proxima_tentativa' => now()->addMinutes(30),
                    'status' => 'pendente',
                    'ultimo_erro' => null,
                ],
            );
        }
    }
}
