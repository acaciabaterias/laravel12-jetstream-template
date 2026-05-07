<?php

namespace App\Jobs;

use App\Models\Bateria;
use App\Models\Deposito;
use App\Models\XmlImportacao;
use App\Services\EstoqueSaldoService;
use App\Services\XmlNfeParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class ProcessXmlImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $xmlImportacaoId) {}

    public function handle(XmlNfeParser $parser, EstoqueSaldoService $estoqueSaldoService): void
    {
        $importacao = XmlImportacao::query()->findOrFail($this->xmlImportacaoId);
        $rawXml = (string) data_get($importacao->payload_xml, 'raw', '');

        try {
            $parsed = $parser->parse($rawXml);
            $itens = $parsed['itens'] ?? [];

            if ($itens === []) {
                throw new RuntimeException('XML sem itens válidos para movimentação.');
            }

            $deposito = Deposito::query()->firstOrCreate(
                ['nome' => 'Principal'],
                ['tipo' => 'principal', 'status' => 'ativo']
            );

            $missingSkus = [];

            foreach ($itens as $item) {
                $bateria = Bateria::query()->where('sku', $item['sku'])->first();

                if (! $bateria) {
                    $missingSkus[] = $item['sku'];

                    continue;
                }

                $estoqueSaldoService->registrarMovimentacao(
                    bateria: $bateria,
                    deposito: $deposito,
                    quantidade: (int) $item['quantidade'],
                    tipoOperacao: 'entrada',
                    user: null,
                    origem: 'compra_xml',
                    justificativa: 'Importacao XML NF-e'
                );
            }

            if ($missingSkus !== []) {
                $importacao->update([
                    'status' => 'pendente',
                    'log_erros' => 'Itens não mapeados por SKU: '.implode(', ', $missingSkus),
                ]);

                return;
            }

            $importacao->update([
                'status' => 'processado',
                'log_erros' => null,
            ]);
        } catch (\Throwable $exception) {
            $importacao->update([
                'status' => 'erro',
                'log_erros' => $exception->getMessage(),
            ]);
        }
    }
}
