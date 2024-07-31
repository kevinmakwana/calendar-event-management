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
    $event = Event::factory()->create([
        'user_id' => $this->user->id,
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
    $event = Event::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Recurring Event',
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => now()->addMonths(1)->toIso8601String(),
    ]);

    $recurringEvent1 = Event::factory()->create([
        'user_id' => $this->user->id,
        'parent_id' => $event->id,
        'start' => now()->addDay()->toIso8601String(),
        'end' => now()->addDay()->addHour()->toIso8601String(),
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => now()->addMonths(1)->toIso8601String(),
    ]);

    $recurringEvent2 = Event::factory()->create([
        'user_id' => $this->user->id,
        'parent_id' => $event->id,
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => now()->addMonths(1)->toIso8601String(),
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
    Event::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Event 1',
        'start' => now()->addHour(),
        'end' => now()->addHours(2),
    ]);

    $event2 = Event::factory()->create([
        'user_id' => $this->user->id,
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

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['start', 'end']);
});

test('user gets validation error when updating event with invalid data:', function (array $eventData, array $expectedErrors) {
    $event = Event::factory()->create();

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
        'frequency should be a string' => [['frequency' => 123], ['frequency']],
        'frequency should be daily, weekly, monthly, or yearly' => [['frequency' => 'invalid-frequency'], ['frequency']],
        'repeat_until is required when recurring_pattern is true' => [['recurring_pattern' => true, 'frequency' => 'daily', 'repeat_until' => null], ['repeat_until']],
        'repeat_until should be a string' => [['recurring_pattern' => true, 'frequency' => 'daily', 'repeat_until' => 123], ['repeat_until']],
        'repeat_until is invalid date format' => [['recurring_pattern' => true, 'frequency' => 'daily', 'repeat_until' => 'invalid-date-format'], ['repeat_until']],
        'repeat_until is before end' => [['recurring_pattern' => true, 'frequency' => 'daily', 'repeat_until' => now()->toIso8601String()], ['repeat_until']],
    ]);

test('user gets validation error when frequency and repeat_until are missing while recurring_pattern is true', function () {
    $event = Event::factory()->create();

    $response = $this->putJson(
        route('events.update', ['event' => $event->id, 'user' => $this->user->id]),
        [
            'title' => 'Recurring Event Update',
            'start' => now()->addHour()->toIso8601String(),
            'end' => now()->addHours(2)->toIso8601String(),
            'recurring_pattern' => true,
            'frequency' => null,
            'repeat_until' => null,
        ]
    );

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['frequency', 'repeat_until']);
});

test('user gets validation error for invalid repeat_until date when recurring pattern is true', function () {
    $event = Event::factory()->create([
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => now()->addMonths(1)->toIso8601String(),
    ]);

    $response = $this->putJson(
        route('events.update', ['event' => $event->id, 'user' => $this->user->id]),
        [
            'title' => 'Updated Recurring Event',
            'start' => now()->addHour()->toIso8601String(),
            'end' => now()->addHours(2)->toIso8601String(),
            'recurring_pattern' => true,
            'frequency' => 'daily',
            'repeat_until' => now()->toIso8601String(),
        ]
    );

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['repeat_until']);
});

test('user gets validation error for invalid frequency value', function () {
    $event = Event::factory()->create([
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => now()->addMonths(1)->toIso8601String(),
    ]);

    $response = $this->putJson(
        route('events.update', ['event' => $event->id, 'user' => $this->user->id]),
        [
            'title' => 'Updated Recurring Event',
            'start' => now()->addHour()->toIso8601String(),
            'end' => now()->addHours(2)->toIso8601String(),
            'recurring_pattern' => true,
            'frequency' => 'invalid-frequency',
            'repeat_until' => now()->addMonths(2)->toIso8601String(),
        ]
    );

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['frequency']);
});
