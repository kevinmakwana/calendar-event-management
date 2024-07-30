<?php

declare(strict_types=1);

use App\Event\Domain\Models\Event;
use App\User\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('user can delete a single event', function () {
    $event = Event::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->deleteJson(
        route('events.destroy', [
            'event' => $event->id,
            'user' => $this->user->id,
        ])
    );
    $response->assertStatus(204);
    $this->assertDatabaseMissing('events', ['id' => $event->id]);
});

test('user can delete a recurring event and its subsequent events', function () {
    $event = Event::factory()->create([
        'user_id' => $this->user->id,
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => now()->addMonths(1),
    ]);

    $subsequentEvents = Event::factory()->count(5)->create([
        'user_id' => $event->user_id,
        'parent_id' => $event->id,
        'recurring_pattern' => true,
        'frequency' => 'daily',
    ]);

    $this->deleteJson(
        route('events.destroy', [
            'event' => $event->id,
            'user' => $this->user->id,
            'deleteSubsequent' => true,
        ])
    )->assertStatus(204);

    $this->assertDatabaseMissing('events', ['id' => $event->id]);

    foreach ($subsequentEvents as $subsequentEvent) {
        $this->assertDatabaseMissing('events', ['id' => $subsequentEvent->id]);
    }
});

test('user gets validation error of 404 when deleting non-existent event', function () {
    $event = Event::factory()->create(
        ['user_id' => $this->user->id]
    );

    $this->deleteJson(
        route('events.destroy', [
            'event' => $event->id,
            'user' => $this->user->id,
        ])
    )->assertStatus(204);

    $this->assertDatabaseMissing('events', ['id' => $event->id]);

    $this->deleteJson(
        route('events.destroy', [
            'event' => $event->id,
            'user' => $event->user_id,
        ])
    )->assertStatus(404);
});
