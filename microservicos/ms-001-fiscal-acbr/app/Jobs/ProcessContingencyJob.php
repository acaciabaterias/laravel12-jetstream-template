<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\ContingenciaQueue;
use App\Models\NotaFiscalJob;
use App\Services\AcbrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessContingencyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $jobId
    ) {}

    public function handle(AcbrService $acbr): void
    {
        $job = NotaFiscalJob::findOrFail($this->jobId);
        $contingencia = ContingenciaQueue::query()->where('nota_id', $job->id)->latest('id')->first();

        try {
            $result = $acbr->emitir($job->payload);

            if ($result['status'] === 'authorized') {
                $job->update([
                    'status' => 'authorized',
                    'chave_acesso' => $result['chave'],
                    'protocolo' => $result['protocolo'],
                    'xml_assinado' => $result['xml'],
                ]);

                if ($contingencia) {
                    $contingencia->update([
                        'status' => 'processed',
                        'ultima_tentativa' => now(),
                    ]);
                }

                AuditLog::query()->create([
                    'nota_id' => $job->id,
                    'acao' => 'CONTINGENCY_RETRY_SUCCESS',
                    'payload_entrada' => $job->payload,
                    'payload_saida' => $result,
                    'status_http' => 200,
                ]);

                $this->notifyErp($job);
            }
        } catch (\Throwable $e) {
            $job->increment('tentativas');
            $job->proxima_tentativa = now()->addMinutes(pow(2, max($job->tentativas, 1)));
            $job->save();

            if ($contingencia) {
                $contingencia->update([
                    'tentativas_realizadas' => $job->tentativas,
                    'ultima_tentativa' => now(),
                    'proxima_tentativa' => $job->proxima_tentativa,
                    'motivo' => $e->getMessage(),
                    'status' => $job->tentativas >= (int) config('acbr.contingency.max_attempts', 10) ? 'critical' : 'pending',
                ]);
            }

            AuditLog::query()->create([
                'nota_id' => $job->id,
                'acao' => 'CONTINGENCY_RETRY_FAILED',
                'payload_entrada' => $job->payload,
                'payload_saida' => ['error' => $e->getMessage()],
                'status_http' => 500,
            ]);

            if ($job->tentativas < (int) config('acbr.contingency.max_attempts', 10)) {
                self::dispatch($this->jobId)->delay($job->proxima_tentativa);
            }
        }
    }

    protected function notifyErp(NotaFiscalJob $job): void
    {
        \Illuminate\Support\Facades\Log::info("Notificando ERP sobre autorização da Nota #{$job->id}");
    }
}
