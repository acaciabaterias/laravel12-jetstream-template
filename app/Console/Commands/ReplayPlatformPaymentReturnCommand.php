<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ProcessPlatformPaymentReturnJob;
use App\Models\RetornoPagamentoSaaS;
use App\Models\UsuarioPlataforma;
use Illuminate\Console\Command;

class ReplayPlatformPaymentReturnCommand extends Command
{
    protected $signature = 'platform-payments:replay-return {return_id : ID do retorno de pagamento} {--operator= : ID do operador autorizado}';

    protected $description = 'Reprocessa um retorno de pagamento SaaS já registrado';

    public function handle(): int
    {
        $retorno = RetornoPagamentoSaaS::query()->find($this->argument('return_id'));
        if (! $retorno) {
            $this->error('Retorno de pagamento não encontrado.');

            return self::FAILURE;
        }

        $operatorId = $this->option('operator');
        if (! $operatorId) {
            $this->error('Informe --operator com um usuário autorizado.');

            return self::FAILURE;
        }

        $operator = UsuarioPlataforma::query()->find($operatorId);
        if (! $operator || ! $operator->hasRole(['super_admin', 'billing'])) {
            $this->error('Operador não autorizado para replay de pagamentos.');

            return self::FAILURE;
        }

        ProcessPlatformPaymentReturnJob::dispatchSync($retorno->id, $operator->id);

        $this->info(sprintf('Replay do retorno %d executado com sucesso.', $retorno->id));

        return self::SUCCESS;
    }
}
