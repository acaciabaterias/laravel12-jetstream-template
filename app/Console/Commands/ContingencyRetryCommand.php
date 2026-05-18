<?php

namespace App\Console\Commands;

use App\Models\FilaContingencia;
use App\Models\Filial;
use App\Services\Gateways\BankGateway;
use App\Services\Gateways\FiscalGateway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContingencyRetryCommand extends Command
{
    protected $signature = 'orquestrador:retry';

    protected $description = 'Processa a fila de contingência fiscal e bancária com backoff exponencial';

    public function handle(FiscalGateway $fiscalGateway, BankGateway $bankGateway): void
    {
        $pendentes = FilaContingencia::query()->where('status', 'pendente')
            ->where('proxima_tentativa', '<=', now())
            ->get();

        if ($pendentes->isEmpty()) {
            $this->info('Nenhum item pendente na fila de contingência.');

            return;
        }

        foreach ($pendentes as $item) {
            $this->info("Processando tentativa #{$item->tentativas} para item ID: {$item->id} ({$item->tipo_integracao})");
            $filial = new Filial;

            $resultado = $item->tipo_integracao === 'fiscal'
                ? $fiscalGateway->emitir($filial, $item->payload)
                : $bankGateway->emitirBoleto($filial, $item->payload, $item->idempotency_key ?? uniqid());

            if (($resultado['status'] ?? null) === 'success') {
                $item->update(['status' => 'concluido']);
                $this->info("Item {$item->id} processado com sucesso.");
            } else {
                $this->tratarFalha($item);
            }
        }
    }

    protected function tratarFalha(FilaContingencia $item): void
    {
        $tentativas = $item->tentativas + 1;

        // Se exceder 10 tentativas -> Alerta Crítico
        if ($tentativas >= 10) {
            $this->alertarSuporte($item);
            $item->update([
                'status' => 'falha_critica',
                'tentativas' => $tentativas,
                'ultimo_erro' => 'Falha critica apos multiplas tentativas.',
            ]);

            return;
        }

        // Backoff Exponencial (em minutos): 1, 5, 15, 30, 60...
        $backoffMap = [1 => 1, 2 => 5, 3 => 15, 4 => 30, 5 => 60, 6 => 120, 7 => 240, 8 => 480, 9 => 1440];
        $minutos = $backoffMap[$tentativas] ?? 1440;

        $item->update([
            'tentativas' => $tentativas,
            'proxima_tentativa' => now()->addMinutes($minutos),
        ]);

        $this->warn("Item {$item->id} falhou. Próxima tentativa em {$minutos} minutos.");
    }

    protected function alertarSuporte(FilaContingencia $item): void
    {
        $numeroSuporte = config('services.suporte.whatsapp');
        $urlMS = config('services.ms_whatsapp.url').'/api/v1/notificacao/enviar';

        $mensagem = "⚠️ *ALERTA CRÍTICO ERP* ⚠️\n\nFalha persistente na integração {$item->tipo_integracao}.\n\nItem na fila há mais de 24h ou excedeu 10 tentativas.\nFavor verificar logs do microserviço.";

        try {
            Http::post($urlMS, [
                'to' => $numeroSuporte,
                'message' => $mensagem,
                'evento' => 'ERRO_ORQUESTRADOR_CRITICO',
            ]);
            Log::error("Alerta crítico de contingência enviado para suporte: {$numeroSuporte}");
        } catch (\Exception $e) {
            Log::emergency('FALHA AO ENVIAR ALERTA DE SUPORTE: '.$e->getMessage());
        }
    }
}
