<?php

declare(strict_types=1);

use App\Event\Domain\Models\Event;
use App\User\Domain\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('user gets validation error when creating event with overlapping times', function () {
    $startDate = Carbon::now()->addDays(1);
    $endDate = $startDate->copy()->addDays(5);

    // Create the first recurring event
    $this->postJson(route('events.store'), [
        'user_id' => $this->user->id,
        'title' => 'Event 1',
        'start' => $startDate->toIso8601String(),
        'end' => $endDate->toIso8601String(),
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => $endDate->copy()->addDays(1)->toIso8601String(),
    ])->assertStatus(201);

    // Attempt to create a second event that overlaps with the first
    $response = $this->postJson(route('events.store'), [
        'user_id' => $this->user->id,
        'title' => 'Event 2',
        'start' => $startDate->toIso8601String(),
        'end' => $endDate->toIso8601String(),
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => $endDate->copy()->addDays(1)->toIso8601String(),
    ]);

    // Check if validation error for overlapping events is returned
    $response->assertStatus(422)
        ->assertJson([
            'errors' => [
                'start' => ['The start time overlaps with another event.'],
                'end' => ['The end time overlaps with another event.'],
            ]
        ]);
});

test('user can create a recurring event and generate multiple entries', function () {
    $start = now()->addHour();
    $end = $start->copy()->addHours(2);

    $response = $this->postJson(route('events.store'), [
        'user_id' => $this->user->id,
        'title' => 'Recurring Event',
        'start' => $start->toIso8601String(),
        'end' => $end->toIso8601String(),
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => now()->addDays(7)->toIso8601String(),
    ]);

    $response->assertStatus(201);

    $events = Event::where([
        'user_id' => $this->user->id,
        'title' => 'Recurring Event',
    ])->get();

    $this->assertCount(7, $events);

    foreach ($events as $index => $event) {
        $expectedStart = $start->copy()->addDays($index)->toIso8601String();
        $expectedEnd = $start->copy()->addDays($index)->addHours(2)->toIso8601String();

        $this->assertEquals($expectedStart, Carbon::parse($event->start)->toIso8601String());
        $this->assertEquals($expectedEnd, Carbon::parse($event->end)->toIso8601String());
    }
});

test('user can create an event without recurring pattern', function () {
    $response = $this->postJson(route('events.store'), [
        'user_id' => $this->user->id,
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

test('user gets validation error when creating event with missing required fields', function (array $eventData, array $expectedErrors) {
    $data = [
        'user_id' => $this->user->id,
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
        ->assertJsonValidationErrors($expectedErrors);
})
    ->with([
        'user_id is missing' => [['user_id' => null], ['user_id']],
        'user_id is required' => [['user_id' => ''], ['user_id']],
        'user_id should be an existing user' => [['user_id' => 123], ['user_id']],
        'title is missing' => [['title' => null], ['title']],
        'title is required' => [['title' => ''], ['title']],
        'title should be a string' => [['title' => 123], ['title']],
        'title should not be more than 255 characters' => [['title' => Str::random(256)], ['title']],
        'description should be a string' => [['description' => 123], ['description']],
        'description should not be more than 1000 characters' => [['description' => Str::random(1001)], ['description']],
        'start is required' => [['start' => ''], ['start']],
        'start is invalid date format' => [['start' => 'invalid-date-format'], ['start']],
        'end is invalid date format' => [['end' => 'invalid-date-format'], ['end']],
        'start is before now' => [['start' => now()->subDay()->toIso8601String()], ['start']],
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
    $response = $this->postJson(route('events.store'), [
        'user_id' => $this->user->id,
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
        'user_id' => $this->user->id,
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
