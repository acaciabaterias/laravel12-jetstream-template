<?php

namespace App\Observers;

use App\Jobs\RecalculateProductReturnIndexJob;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\OrdemServicoGarantia;
use App\Models\Vale;
use App\Models\ItemVale;
use Illuminate\Support\Facades\Log;

class OrdemServicoGarantiaObserver
{
    /**
     * Handle status changes and result evaluation.
     */
    public function updated(OrdemServicoGarantia $os): void
    {
        // 1. Notificações WhatsApp em mudança de status (FR-GAR-03)
        if ($os->wasChanged('status')) {
            $msg = $this->getMensagemStatus($os->status);
            if ($msg) {
                SendWhatsAppNotificationJob::dispatch($os->id, $msg);
            }
        }

        // 2. Lógica de Improcedência (FR-GAR-05)
        if ($os->wasChanged('resultado') && $os->resultado === 'improcedente') {
            $this->gerarCobrancaServico($os);
        }

        // 3. Recálculo de Índice de Retorno (FR-GAR-04)
        if ($os->wasChanged('resultado') || $os->wasChanged('status') && $os->status === 'concluida') {
            RecalculateProductReturnIndexJob::dispatch($os->bateria_id);
        }
    }

    protected function getMensagemStatus(string $status): ?string
    {
        return match ($status) {
            'em_avaliacao' => "Sua bateria entrou em análise técnica. Avisaremos assim que o laudo estiver pronto.",
            'pronta' => "O laudo da sua bateria está pronto para retirada em nossa base.",
            'negada' => "Sua garantia foi negada após análise técnica. Entre em contato para mais detalhes.",
            'concluida' => "Sua ordem de serviço de garantia foi finalizada com sucesso.",
            default => null,
        };
    }

    /**
     * Gera um Vale de serviço de recarga/mão de obra para laudos improcedentes.
     */
    protected function gerarCobrancaServico(OrdemServicoGarantia $os): void
    {
        // Cria um Vale de Serviço para cobrar o "Laudo de Improcedência / Recarga"
        $vale = Vale::create([
            'cliente_id' => $os->cliente_id,
            'filial_id' => $os->filial_id,
            'vendedor_id' => auth()->id() ?? User::first()->id, // Fallback p/ jobs
            'status' => 'aberto',
            'observacoes' => "Cobrança gerada por Garantia Improcedente (OS #{$os->id})",
        ]);

        // Aqui assumimos um serviço genérico de recarga ou taxa técnica
        // Logica simplificada: cria uma entrada de texto ou um produto "Servico" se existir
        Log::info("Cobrança de improcedência gerada para OS #{$os->id}", ['vale_id' => $vale->id]);
    }
}
