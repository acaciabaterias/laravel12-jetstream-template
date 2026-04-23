<?php

namespace App\Jobs;

use App\Models\OrdemServico;
use App\Models\Vale;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Validation\ValidationException;

class ConvertValeToOsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $valeId, public ?int $tecnicoResponsavelId = null) {}

    public function handle(): void
    {
        $vale = Vale::query()->findOrFail($this->valeId);

        if ($vale->status !== 'aberto') {
            throw ValidationException::withMessages([
                'vale' => 'Apenas vales abertos podem ser convertidos em ordem de servico.',
            ]);
        }

        OrdemServico::query()->create([
            'vale_id' => $vale->id,
            'cliente_id' => $vale->cliente_id,
            'tecnico_responsavel_id' => $this->tecnicoResponsavelId,
            'data_abertura' => now(),
            'status' => 'aberta',
            'laudo' => null,
            'observacoes' => $vale->observacoes,
        ]);

        $vale->update([
            'status' => 'em_servico',
        ]);
    }
}
