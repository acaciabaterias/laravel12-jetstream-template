<?php

namespace App\Jobs;

use App\Models\OrdemServico;
use App\Models\Vale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ConvertValeToOsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $valeId;

    public $userId;

    public $filialId;

    public function __construct(int $valeId, int $userId, int $filialId)
    {
        $this->valeId = $valeId;
        $this->userId = $userId;
        $this->filialId = $filialId;
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $vale = Vale::findOrFail($this->valeId);

            if ($vale->status !== 'aberto') {
                throw new \Exception('Apenas vales abertos podem virar Ordens de Serviço.');
            }

            // A Reserva de estoque é MANTIDA. Só baixamos quando a OS for concluída como "Troca efetiva".
            // Para isso o Vale converte para 'em_os'.

            OrdemServico::create([
                'vale_id' => $vale->id,
                'cliente_id' => $vale->cliente_id,
                'filial_id' => $this->filialId,
                'status' => 'aberta',
                'prioridade' => 'alta',
                'observacoes' => 'OS Despachada automaticamente via Balcão de Vendas (PDV).',
            ]);

            $vale->status = 'em_os';
            $vale->save();
        });
    }
}
