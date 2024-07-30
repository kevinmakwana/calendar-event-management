<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom([
            database_path('migrations'),
            app_path('User/Infrastructure/Migrations'),
        ]);
    }
}
