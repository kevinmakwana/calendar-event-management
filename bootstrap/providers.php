<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\User\Infrastructure\Providers\UserServiceProvider::class,
    App\Event\Infrastructure\Providers\EventServiceProvider::class,
];
