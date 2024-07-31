<?php

declare(strict_types=1);

namespace App\Event\Presentation\API;

use App\Event\Presentation\API\Controllers\EventController;
use Illuminate\Support\Facades\Route;

/**
 * Routes for managing events.
 */
Route::prefix('api/events')
    ->as('events.')
    ->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::post('/', [EventController::class, 'store'])->name('store');
        Route::put('/{event}/users/{user}', [EventController::class, 'update'])->name('update');
        Route::delete('/{event}/users/{user}', [EventController::class, 'destroy'])->name('destroy');
    });
