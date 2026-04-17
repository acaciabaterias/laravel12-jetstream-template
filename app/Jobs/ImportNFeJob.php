<?php

namespace App\Jobs;

use App\Models\Bateria;
use App\Models\EstoqueMovimentacao;
use App\Models\Fornecedor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ImportNFeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filialId;
    public $depositoId;
    public $userId;
    public $chaveNfe;
    public $fornecedorDados;
    public $itensMapeados;

    /**
     * Create a new job instance.
     */
    public function __construct(int $filialId, int $depositoId, int $userId, string $chaveNfe, array $fornecedorDados, array $itensMapeados)
    {
        $this->filialId = $filialId;
        $this->depositoId = $depositoId;
        $this->userId = $userId;
        $this->chaveNfe = $chaveNfe;
        $this->fornecedorDados = $fornecedorDados;
        $this->itensMapeados = $itensMapeados;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            // Find or Create the Supplier based on CNPJ and Filial Isolation naturally handled
            $fornecedor = Fornecedor::firstOrCreate(
                ['cnpj' => $this->fornecedorDados['cnpj'], 'filial_id' => $this->filialId],
                ['nome' => $this->fornecedorDados['nome']]
            );

            foreach ($this->itensMapeados as $item) {
                // Skip unmapped entries
                if (empty($item['bateria_id'])) continue;

                // Insert Event-Sourced transaction. Our Observer will trap this and update 'estoque_saldos'.
                EstoqueMovimentacao::create([
                    'bateria_id' => $item['bateria_id'],
                    'filial_id' => $this->filialId,
                    'deposito_id' => $this->depositoId,
                    'user_id' => $this->userId,
                    'tipo' => 'entrada',
                    'quantidade' => (int) $item['quantidade'],
                    'origem' => 'NF',
                    'referencia' => $this->chaveNfe,
                ]);

                // Phase 4: Logistics "Conta Sucata" accumulation handling
                $bateria = Bateria::find($item['bateria_id']);
                if ($bateria && $bateria->peso_sucata_kg) {
                    // Fornecedor delivered good batteries, we now 'owe' them scrap batteries back if explicitly tracked.
                    // Or they owe us? Usually, Supplier sends new batteries without scrap if we didn't return immediately.
                    // Standard logic implies we now owe the supplier the scrap: saldo negative/debit.
                    $fornecedor->saldo_sucata_kg -= ((float) $bateria->peso_sucata_kg * $item['quantidade']);
                    if ($bateria->valor_base_sucata_kg) {
                        $fornecedor->saldo_sucata_financeiro -= (($bateria->peso_sucata_kg * $bateria->valor_base_sucata_kg) * $item['quantidade']);
                    }
                }
            }

            $fornecedor->save();
        });
    }
}
