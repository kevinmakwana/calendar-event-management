<?php

declare(strict_types=1);

use App\Event\Domain\Models\Event;
use App\User\Domain\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('user can create an event without recurring pattern', function () {
    $user = User::factory()->create();
    $response = $this->postJson(route('events.store'), [
        'user_id' => $user->id,
        'title' => 'Sample Event',
        'description' => 'This is a sample event',
        'start' => now()->addHour()->toIso8601String(),
        'end' => now()->addHours(2)->toIso8601String(),
        'recurring_pattern' => false,
        'frequency' => null,
        'repeat_until' => null,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'user',
                'title',
                'description',
                'start',
                'end',
                'recurring_pattern',
                'frequency',
                'repeat_until',
                'created_at',
                'updated_at',
            ],
        ]);
});

test('user can create a recurring event and generate multiple entries', function () {
    $user = User::factory()->create();
    $start = now()->addHour();
    $end = $start->copy()->addHours(2);

    $response = $this->postJson(route('events.store'), [
        'user_id' => $user->id,
        'title' => 'Recurring Event',
        'start' => $start->toIso8601String(),
        'end' => $end->toIso8601String(),
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => now()->addDays(7)->toIso8601String(),
    ]);

    $response->assertStatus(201);

    $events = Event::where([
        'user_id' => $user->id,
        'title' => 'Recurring Event',
    ])->get();

    $this->assertCount(7, $events);

    foreach ($events as $index => $event) {
        $expectedStart = $start->copy()->addDays($index)->toIso8601String();
        $expectedEnd = $start->copy()->addDays($index)->addHours(2)->toIso8601String();

        $eventStart = Carbon::parse($event->start)->toIso8601String();
        $eventEnd = Carbon::parse($event->end)->toIso8601String();

        $this->assertEquals($expectedStart, $eventStart);
        $this->assertEquals($expectedEnd, $eventEnd);
    }
});

test('user gets validation error when creating event with overlapping times', function () {
    $user = User::factory()->create();

    Event::factory()->create([
        'user_id' => $user->id,
        'title' => 'Existing Event',
        'start' => now()->addHour(),
        'end' => now()->addHours(2),
    ]);

    $response = $this->postJson(route('events.store'), [
        'user_id' => $user->id,
        'title' => 'Overlapping Event',
        'description' => 'This event overlaps with an existing event',
        'start' => now()->addHour()->toIso8601String(),
        'end' => now()->addHours(2)->toIso8601String(),
        'recurring_pattern' => false,
        'frequency' => null,
        'repeat_until' => null,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['start', 'end']);
});

test('user gets validation error when creating event with missing required fields', function (array $eventData) {
    $data = [
        'title' => 'Default Event Title',
        'description' => 'Default Description',
        'start' => now()->addDay()->toIso8601String(),
        'end' => now()->addDay()->addHour()->toIso8601String(),
        'recurring_pattern' => false,
        'frequency' => null,
        'repeat_until' => null,
    ];

    $data = array_merge($data, $eventData);

    $response = $this->postJson(route('events.store'), $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(array_keys($eventData));
})
    ->with([
        'title is required' => [['title' => '']],
        'title should be a string' => [['title' => 123]],
        'title should not be more than 255 characters' => [['title' => Str::random(256)]],
        'start is invalid date format' => [['start' => 'invalid-date-format']],
        'end is invalid date format' => [['end' => 'invalid-date-format']],
        'start is before now' => [['start' => now()->subDay()->toIso8601String()]],
        'end is before start' => [['end' => now()->toIso8601String()]],
        'invalid recurring pattern' => [['recurring_pattern' => 'true']],
        'invalid frequency' => [['frequency' => 'invalid-frequency']],
        'repeat_until is invalid date format' => [['repeat_until' => 'invalid-date-format']],
        'repeat_until is before end' => [['repeat_until' => now()->toIso8601String()]],
    ]);

test('user gets validation error when frequency and repeat_until are missing while recurring_pattern is true', function () {
    $response = $this->postJson(route('events.store'), [
        'title' => 'Recurring Event',
        'start' => now()->addHour()->toIso8601String(),
        'end' => now()->addHours(2)->toIso8601String(),
        'recurring_pattern' => true,
        'frequency' => null,
        'repeat_until' => null,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['frequency', 'repeat_until']);
});

test('user gets validation error when repeat_until is before end date for recurring events', function () {
    $response = $this->postJson(route('events.store'), [
        'title' => 'Recurring Event',
        'start' => now()->addHour()->toIso8601String(),
        'end' => now()->addHours(2)->toIso8601String(),
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => now()->toIso8601String(), // Before end date
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['repeat_until']);
});

test('user gets validation error for invalid frequency value', function () {
    $response = $this->postJson(route('events.store'), [
        'title' => 'Recurring Event',
        'start' => now()->addHour()->toIso8601String(),
        'end' => now()->addHours(2)->toIso8601String(),
        'recurring_pattern' => true,
        'frequency' => 'invalid-frequency', // Invalid frequency
        'repeat_until' => now()->addMonths(1)->toIso8601String(),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['frequency']);
});
