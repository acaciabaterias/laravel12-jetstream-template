<?php

namespace App\Jobs;

use App\Models\CnabRetornoUpload;
use App\Services\CnabOrchestratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchCnabProcessingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $cnabRetornoUploadId) {}

    public function handle(CnabOrchestratorService $cnabOrchestratorService): void
    {
        $upload = CnabRetornoUpload::query()->findOrFail($this->cnabRetornoUploadId);
        $result = $cnabOrchestratorService->processarRetorno('dummy-cnab', null);

        $upload->update([
            'status_processamento' => $result['status'] === 'success' ? 'processado' : 'erro',
            'log_processamento' => json_encode($result),
        ]);
    }
}
