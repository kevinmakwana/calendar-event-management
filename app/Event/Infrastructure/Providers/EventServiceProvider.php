<?php

declare(strict_types=1);

namespace App\Event\Infrastructure\Providers;

use App\Event\Domain\Interfaces\EventRepositoryInterface;
use App\Event\Infrastructure\Repositories\EventRepository;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // App\Event\Presentation\API
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/API/routes.php');

        $this->loadMigrationsFrom([
            database_path('migrations'),
            app_path('Event/Infrastructure/Migrations'),
        ]);
    }
}
