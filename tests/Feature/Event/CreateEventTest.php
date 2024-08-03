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

test('events with non-overlapping recurring occurrences are allowed', function () {
    // Create Event A: Recurs every month for a year
    $eventAStart = now()->startOfMonth()->toIso8601String();
    $eventAEnd = now()->startOfMonth()->addHours(1)->toIso8601String();
    Event::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Monthly Recurring Event',
        'start' => $eventAStart,
        'end' => $eventAEnd,
        'recurring_pattern' => true,
        'frequency' => 'monthly',
        'repeat_until' => now()->addYear()->toIso8601String(),
    ]);

    // Create Event B: Starts after Event A's recurrence period
    $eventBStart = now()->addMonths(2)->startOfDay()->toIso8601String();
    $eventBEnd = now()->addMonths(2)->startOfDay()->addHours(1)->toIso8601String();
    $response = $this->postJson(
        route('events.store'),
        [
            'user_id' => $this->user->id,
            'title' => 'Non-Overlapping Recurring Event',
            'start' => $eventBStart,
            'end' => $eventBEnd,
            'recurring_pattern' => true,
            'frequency' => 'monthly',
            'repeat_until' => now()->addYear()->toIso8601String(),
        ]
    );
    // Assert that the response status is 201 and the event was created
    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Event created successfully.',
        ]);

    $events = Event::where([
        'user_id' => $this->user->id,
        'title' => 'Non-Overlapping Recurring Event',
    ])->get();

    $this->assertCount(12, $events);
});

test('overlapping recurring occurrences with different recurrence rules are not allowed', function () {
    $now = Carbon::now()->addMinutes(10);

    // Create Event A: Recurs every week for a year
    $eventAStart = $now->addDay()->toIso8601String();
    $eventAEnd = Carbon::parse($eventAStart)->addHour()->toIso8601String();
    Event::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Weekly Recurring Event',
        'start' => $eventAStart,
        'end' => $eventAEnd,
        'recurring_pattern' => true,
        'frequency' => 'weekly',
        'repeat_until' => $now->addYear()->toIso8601String(),
    ]);

    // Create Event B: Recurs daily, overlapping Event A
    $eventBStart = Carbon::parse($eventAStart)->subDay()->toIso8601String();
    $eventBEnd = Carbon::parse($eventBStart)->addHour()->toIso8601String();
    $response = $this->postJson(
        route('events.store'),
        [
            'user_id' => $this->user->id,
            'title' => 'Daily Recurring Event',
            'start' => $eventBStart,
            'end' => $eventBEnd,
            'recurring_pattern' => true,
            'frequency' => 'daily',
            'repeat_until' => $now->addMonth()->toIso8601String(),
        ]
    );

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'The start time & end time overlaps with a recurring event.',
            'errors' => [
                'start' => ['The start time overlaps with a recurring event.'],
                'end' => ['The end time overlaps with a recurring event.'],
            ],
        ]);
});

test('events with overlapping recurring occurrences are not allowed', function () {
    $now = now();

    // Create Event A: Recurs every month for a year
    $eventAStart = $now->addDays(1)->startOfMonth()->toIso8601String();
    $eventAEnd = $now->addDays(1)->startOfMonth()->addHours(1)->toIso8601String();
    Event::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Monthly Recurring Event',
        'start' => $eventAStart,
        'end' => $eventAEnd,
        'recurring_pattern' => true,
        'frequency' => 'monthly',
        'repeat_until' => $now->addYear()->toIso8601String(),
    ]);

    // Create Event B: Recurs daily for a week, starting a day before Event A
    $eventBStart = $now->addDays(1)->subDay()->startOfDay()->toIso8601String();
    $eventBEnd = $now->addDays(1)->subDay()->startOfDay()->addHours(1)->toIso8601String();
    Event::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Daily Recurring Event',
        'start' => $eventBStart,
        'end' => $eventBEnd,
        'recurring_pattern' => true,
        'frequency' => 'daily',
        'repeat_until' => $now->addWeek()->toIso8601String(),
    ]);

    // Attempt to create an overlapping event with the same time period
    $response = $this->postJson(
        route('events.store'),
        [
            'user_id' => $this->user->id,
            'title' => 'Overlapping Recurring Event',
            'start' => $eventBStart,
            'end' => $eventBEnd,
            'recurring_pattern' => true,
            'frequency' => 'daily',
            'repeat_until' => $now->addWeek()->toIso8601String(),
        ]
    );
    // Assert that the response status is 422 and overlaps are detected
    $response->assertStatus(422)
        ->assertJson([
            'message' => 'The start time & end time overlaps with a recurring event.',
            'errors' => [
                'start' => ['The start time overlaps with a recurring event.'],
                'end' => ['The end time overlaps with a recurring event.'],
            ],
        ]);
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
