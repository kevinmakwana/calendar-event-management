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
        $this->app->bind(EventService::class, function ($app) {
            return new EventService($app->make(EventRepositoryInterface::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Factory::guessFactoryNamesUsing(function (string $modelName): string {
            // Ensure the return type is a class-string<Illuminate\Database\Eloquent\Factories\Factory>
            return 'App\\Event\\Domain\\Factories\\' . class_basename($modelName) . 'Factory';
        });

        Response::macro('apiResponse', function ($data = null, $message = '', $status = 200) {
            return response()->json([
                'data' => $data,
                'message' => $message,
                'status' => $status,
            ], $status);
        });
    }
}
