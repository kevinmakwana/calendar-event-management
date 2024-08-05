<?php

declare(strict_types=1);

namespace App\Providers;

use App\Event\Application\Services\EventService;
use App\Event\Domain\Interfaces\EventRepositoryInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EventService::class, fn ($app) => new EventService($app->make(EventRepositoryInterface::class)));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Factory::guessFactoryNamesUsing(fn (string $modelName): string =>
            // Ensure the return type is a class-string<Illuminate\Database\Eloquent\Factories\Factory>
            'App\\Event\\Domain\\Factories\\'.class_basename($modelName).'Factory');

        Response::macro('apiResponse', fn ($data = null, $message = '', $status = 200) => response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $status,
        ], $status));
    }
}
