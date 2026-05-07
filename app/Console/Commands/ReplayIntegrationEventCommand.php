<?php

namespace App\Console\Commands;

use App\Models\EntregaIntegracao;
use App\Models\User;
use App\Services\Contracts\Integration\IntegrationReplayServiceContract;
use Illuminate\Console\Command;

class ReplayIntegrationEventCommand extends Command
{
    protected $signature = 'integration:replay {delivery_id : ID da entrega de integração} {--operator= : ID do operador autorizado}';

    protected $description = 'Reenfileira uma entrega de integração em falha ou dead-letter';

    public function handle(IntegrationReplayServiceContract $replayService): int
    {
        $delivery = EntregaIntegracao::query()->find($this->argument('delivery_id'));
        if (! $delivery) {
            $this->error('Entrega de integração não encontrada.');

            return self::FAILURE;
        }

        $operatorId = $this->option('operator');
        if (! $operatorId) {
            $this->error('Informe --operator com um usuário autorizado.');

            return self::FAILURE;
        }

        $operator = User::query()->find($operatorId);
        if (! $operator) {
            $this->error('Operador não encontrado.');

            return self::FAILURE;
        }

        try {
            $replay = $replayService->replay($delivery, $operator, [
                'source' => 'artisan-command',
            ]);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Replay registrado com sucesso. Nova entrega: %d', $replay->id));

        return self::SUCCESS;
    }
}
