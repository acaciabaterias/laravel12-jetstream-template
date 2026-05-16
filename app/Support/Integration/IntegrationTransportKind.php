<?php

namespace App\Support\Integration;

enum IntegrationTransportKind: string
{
    case Broker = 'broker';
    case Webhook = 'webhook';
    case Gateway = 'gateway';
    case Manual = 'manual';
}
