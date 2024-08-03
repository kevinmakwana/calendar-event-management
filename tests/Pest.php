<?php

declare(strict_types=1);

use App\Event\Domain\Models\Event;
use App\User\Domain\Models\User;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(
    Tests\TestCase::class,
    // Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

/**
 * Helper method to create an event.
 */
function createEvent(User $user, array $attributes = []): Event
{
    return Event::factory()->create(array_merge([
        'user_id' => $user->id,
        'recurring_pattern' => false,
        'frequency' => null,
        'repeat_until' => null,
    ], $attributes));
}
