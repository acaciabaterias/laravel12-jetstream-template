<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Bateria;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disparado quando o saldo de uma bateria fica abaixo do limiar operacional.
 */
class EstoqueBaixoEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Bateria $bateria, public int $saldo_atual) {}
}
