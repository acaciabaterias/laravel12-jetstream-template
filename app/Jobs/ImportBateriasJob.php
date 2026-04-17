<?php

namespace App\Jobs;

use App\Models\Bateria;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportBateriasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $records;
    public $filialId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $records, int $filialId)
    {
        $this->records = $records;
        $this->filialId = $filialId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Execute without triggering the standard Eloquent events (like Auditable)
        // to ensure high performance during mass import batches.
        Bateria::withoutEvents(function () {
            foreach ($this->records as $row) {
                try {
                    // Skip invalid basic structure
                    if (empty($row['sku']) || empty($row['marca'])) {
                        continue;
                    }

                    Bateria::updateOrCreate(
                        [
                            'sku' => $row['sku'], 
                            'filial_id' => $this->filialId
                        ],
                        [
                            'marca' => $row['marca'],
                            'tecnologia' => $row['tecnologia'] ?? null,
                            'amperagem' => isset($row['amperagem']) ? (int) $row['amperagem'] : null,
                            'polo' => $row['polo'] ?? null,
                            'preco_venda' => isset($row['preco_venda']) ? (float) $row['preco_venda'] : 0,
                            'peso_sucata_kg' => isset($row['peso_sucata_kg']) ? (float) $row['peso_sucata_kg'] : null,
                            'valor_base_sucata_kg' => isset($row['valor_base_sucata_kg']) ? (float) $row['valor_base_sucata_kg'] : null,
                        ]
                    );
                } catch (\Exception $e) {
                    // Skip specific rows that trigger unique DB constraints or casting issues 
                    // and log silently without failing the whole chunk.
                    Log::warning("ImportBateriasJob: Failed to import SKU {$row['sku']} - " . $e->getMessage());
                }
            }
        });
    }
}
