<?php

use App\Providers\AppServiceProvider;
use App\Providers\CollectionMacrosServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\JetstreamServiceProvider;
use App\Providers\RequestMacrosServiceProvider;
use App\Providers\StrMacrosServiceProvider;
use App\Providers\VoltServiceProvider;
use L5Swagger\L5SwaggerServiceProvider;

return [
    AppServiceProvider::class,
    CollectionMacrosServiceProvider::class,
    EventServiceProvider::class,
    FortifyServiceProvider::class,
    JetstreamServiceProvider::class,
    RequestMacrosServiceProvider::class,
    StrMacrosServiceProvider::class,
    VoltServiceProvider::class,
    L5SwaggerServiceProvider::class,
];
