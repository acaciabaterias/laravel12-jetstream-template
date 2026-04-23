<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\OrdemServicoGarantia;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disparado quando uma ordem de servico/garantia e concluida.
 */
class OSConcluidaEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public OrdemServicoGarantia $os) {}
}
