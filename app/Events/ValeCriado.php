<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Vale;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disparado quando um vale comercial e criado.
 */
class ValeCriado
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Vale $vale) {}
}
