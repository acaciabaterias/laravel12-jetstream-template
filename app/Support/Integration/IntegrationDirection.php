<?php

namespace App\Support\Integration;

enum IntegrationDirection: string
{
    case Outbound = 'outbound';
    case Inbound = 'inbound';
}
