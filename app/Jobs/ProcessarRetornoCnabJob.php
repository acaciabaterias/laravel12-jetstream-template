<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

/**
 * Encaminha o arquivo de retorno CNAB para o microservico bancario.
 */
class ProcessarRetornoCnabJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $arquivo_base64,
        public int|string $banco_id,
    ) {}

    public function handle(): void
    {
        Http::post(rtrim((string) config('services.ms_bancario.url', 'http://localhost:8002'), '/').'/v1/cnab/retorno', [
            'arquivo' => $this->arquivo_base64,
            'banco_id' => $this->banco_id,
        ])->throw();
    }
}
