<?php

declare(strict_types=1);

use App\Event\Domain\Models\Event;
use App\User\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('user can update a single event', function () {
    $event = createEvent($this->user, [
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => now()->addMonths(1)->toIso8601String(),
    ]);

    $newStart = now()->addHour()->toIso8601String();
    $newEnd = now()->addHours(2)->toIso8601String();
    $newRepeatUntil = now()->addMonths(2)->toIso8601String();

    $this->putJson(
        route('events.update', ['event' => $event->id, 'user' => $this->user->id]),
        [
            'title' => $event->title,
            'start' => $newStart,
            'end' => $newEnd,
            'recurring_pattern' => true,
            'frequency' => 'weekly',
            'repeat_until' => $newRepeatUntil,
        ]
    )->assertStatus(200)
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

    $event = Event::find($event->id);

    expect($event)->toMatchArray([
        'start' => $newStart,
        'end' => $newEnd,
        'recurring_pattern' => true,
        'frequency' => 'weekly',
        'repeat_until' => $newRepeatUntil,
    ]);
});

test('user can update all recurring events', function () {
    $event = createEvent($this->user, [
        'title' => 'Recurring Event',
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => now()->addMonths(1)->toIso8601String(),
    ]);

    $recurringEvent1 = createEvent($this->user, [
        'parent_id' => $event->id,
        'start' => now()->addDay()->toIso8601String(),
        'end' => now()->addDay()->addHour()->toIso8601String(),
    ]);

    $recurringEvent2 = createEvent($this->user, [
        'parent_id' => $event->id,
        'start' => now()->addDays(2)->toIso8601String(),
        'end' => now()->addDays(2)->addHour()->toIso8601String(),
    ]);

    $newStart = now()->addHour()->toIso8601String();
    $newEnd = now()->addHours(2)->toIso8601String();
    $newRepeatUntil = now()->addMonths(2)->toIso8601String();

    $response = $this->putJson(
        route('events.update', ['event' => $event->id, 'user' => $this->user->id]),
        [
            'title' => $event->title,
            'start' => $newStart,
            'end' => $newEnd,
            'recurring_pattern' => true,
            'frequency' => 'weekly',
            'repeat_until' => $newRepeatUntil,
        ]
    );

    $response->assertStatus(200);
    // dd(Event::find($event->id)->toArray(), $this->user->id);
    expect(Event::find($event->id))->toMatchArray([
        'user_id' => $this->user->id,
        'start' => $newStart,
        'end' => $newEnd,
        'recurring_pattern' => true,
        'frequency' => 'weekly',
        'repeat_until' => $newRepeatUntil,
    ]);

    expect(Event::find($recurringEvent1->id))->toMatchArray([
        'user_id' => $this->user->id,
        'parent_id' => $event->id,
        'start' => $newStart,
        'end' => $newEnd,
        'recurring_pattern' => false,
        'frequency' => null,
        'repeat_until' => null,
    ]);

    expect(Event::find($recurringEvent2->id))->toMatchArray([
        'user_id' => $this->user->id,
        'parent_id' => $event->id,
        'start' => $newStart,
        'end' => $newEnd,
        'recurring_pattern' => false,
        'frequency' => null,
        'repeat_until' => null,
    ]);
});

test('user gets validation error when updating event with overlapping times', function () {
    // Create an initial event that overlaps
    createEvent($this->user, [
        'title' => 'Event 1',
        'start' => now()->addHour(),
        'end' => now()->addHours(2),
    ]);

    // Create another event to test overlap
    $event2 = createEvent($this->user, [
        'title' => 'Event 2',
        'start' => now()->addDays(1),
        'end' => now()->addDays(1)->addHours(1),
    ]);

    $response = $this->putJson(
        route('events.update', ['event' => $event2->id, 'user' => $this->user->id]),
        [
            'title' => 'Updated Overlapping Event',
            'description' => 'This update causes an overlap',
            'start' => now()->addHour()->toIso8601String(),
            'end' => now()->addHours(2)->toIso8601String(),
            'recurring_pattern' => false,
            'frequency' => null,
            'repeat_until' => null,
        ]
    );
    // Adjust the test to match the actual response structure
    $response->assertStatus(422)
        ->assertJson([
            'message' => 'The start time & end time overlaps with another event.',
            'errors' => [
                'start' => ['The start time overlaps with another event.'],
                'end' => ['The end time overlaps with another event.'],
            ],
        ]);
});

test('user gets validation error when updating event with invalid data:', function (array $eventData, array $expectedErrors) {
    $event = createEvent($this->user);

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

    $response = $this->putJson(
        route('events.update', ['event' => $event->id, 'user' => $this->user->id]),
        $data
    );

    $response->assertStatus(422)
        ->assertJsonValidationErrors($expectedErrors);
})
    ->with([
        'title is missing' => [['title' => null], ['title']],
        'title is required' => [['title' => ''], ['title']],
        'title is too long' => [['title' => Str::random(256)], ['title']],
        'title should be a string' => [['title' => 123], ['title']],
        'description is missing' => [['description' => null], ['description']],
        'description is required' => [['description' => ''], ['description']],
        'description is too long' => [['description' => Str::random(1001)], ['description']],
        'description should be a string' => [['description' => 123], ['description']],
        'start is missing' => [['start' => null], ['start']],
        'start is required' => [['start' => ''], ['start']],
        'start is invalid date format' => [['start' => 'invalid-date-format'], ['start']],
        'start is before now' => [['start' => now()->subDay()->toIso8601String()], ['start']],
        'end is missing' => [['end' => null], ['end']],
        'end is required' => [['end' => ''], ['end']],
        'end is invalid date format' => [['end' => 'invalid-date-format'], ['end']],
        'end is before start' => [['end' => now()->toIso8601String()], ['end']],
        'invalid recurring pattern' => [['recurring_pattern' => 'true'], ['recurring_pattern']],
        'frequency required when recurring_pattern is true' => [['recurring_pattern' => true, 'frequency' => null, 'repeat_until' => now()->addMonth()->toIso8601String()], ['frequency']],
        'frequency should be a string' => [['recurring_pattern' => true, 'frequency' => 123], ['frequency']],
        'repeat_until is missing' => [['recurring_pattern' => true, 'repeat_until' => null], ['repeat_until']],
        'repeat_until is required' => [['recurring_pattern' => true, 'repeat_until' => ''], ['repeat_until']],
        'repeat_until is invalid date format' => [['recurring_pattern' => true, 'repeat_until' => 'invalid-date-format'], ['repeat_until']],
        'repeat_until is before start' => [['recurring_pattern' => true, 'repeat_until' => now()->subDay()->toIso8601String()], ['repeat_until']],
    ]);

test('user gets validation error when frequency and repeat_until are missing while recurring_pattern is true', function () {
    $event = createEvent($this->user, [
        'recurring_pattern' => true,
    ]);

    $response = $this->putJson(
        route('events.update', ['event' => $event->id, 'user' => $this->user->id]),
        [
            'title' => $event->title,
            'start' => now()->addDay()->toIso8601String(),
            'end' => now()->addDay()->addHour()->toIso8601String(),
            'recurring_pattern' => true,
            'frequency' => null,
            'repeat_until' => null,
        ]
    );

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['frequency', 'repeat_until']);
});

test('user gets validation error for invalid repeat_until date when recurring pattern is true', function () {
    $event = createEvent($this->user, [
        'recurring_pattern' => true,
        'frequency' => 'daily',
    ]);

    $response = $this->putJson(
        route('events.update', ['event' => $event->id, 'user' => $this->user->id]),
        [
            'title' => $event->title,
            'start' => now()->addDay()->toIso8601String(),
            'end' => now()->addDay()->addHour()->toIso8601String(),
            'recurring_pattern' => true,
            'frequency' => 'daily',
            'repeat_until' => 'invalid-date-format',
        ]
    );

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['repeat_until']);
});

test('user gets validation error for invalid frequency value', function () {
    $event = createEvent($this->user, [
        'recurring_pattern' => true,
        'frequency' => 'invalid-frequency',
        'repeat_until' => now()->addMonth()->toIso8601String(),
    ]);

    $response = $this->putJson(
        route('events.update', ['event' => $event->id, 'user' => $this->user->id]),
        [
            'title' => $event->title,
            'start' => now()->addDay()->toIso8601String(),
            'end' => now()->addDay()->addHour()->toIso8601String(),
            'recurring_pattern' => true,
            'frequency' => 'invalid-frequency',
            'repeat_until' => now()->addMonth()->toIso8601String(),
        ]
    );

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['frequency']);
});
