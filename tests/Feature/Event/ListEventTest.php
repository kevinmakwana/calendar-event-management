<?php

declare(strict_types=1);

use App\Event\Domain\Models\Event;
use App\User\Domain\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('user can list paginated events in a specific time range', function () {
    Event::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Event 1',
        'start' => now()->addDay()->toIso8601String(),
        'end' => now()->addDays(1)->addHours(1)->toIso8601String(),
    ]);

    Event::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Event 2',
        'start' => now()->addDays(2)->toIso8601String(),
        'end' => now()->addDays(2)->addHours(1)->toIso8601String(),
    ]);

    $response = $this->getJson(
        route('events.index', ['user_id' => $this->user->id]) . '&start=' . now()->toDateTimeString() . '&end=' . now()->addDays(7)->toDateTimeString()
    );

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'user',
                    'title',
                    'description',
                    'start',
                    'end',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
});

test('user gets empty array of data when no events match the date range', function () {
    Event::factory()->create([
        'user_id' => $this->user->id,
        'start' => now()->addDays(10)->toIso8601String(),
        'end' => now()->addDays(10)->addHours(1)->toIso8601String(),
    ]);

    $this->getJson(
        route('events.index', ['user_id' => $this->user->id]) . '&start=' . now()->toDateTimeString() . '&end=' . now()->addDays(5)->toDateTimeString()
    )
        ->assertStatus(200)
        ->assertJson([
            'data' => [],
            'links' => [
                'first' => route('events.index') . '?page=1',
                'last' => route('events.index') . '?page=1',
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                'current_page' => 1,
                'from' => null,
                'last_page' => 1,
                'links' => [
                    [
                        'url' => null,
                        'label' => '&laquo; Previous',
                        'active' => false,
                    ],
                    [
                        'url' => route('events.index') . '?page=1',
                        'label' => '1',
                        'active' => true,
                    ],
                    [
                        'url' => null,
                        'label' => 'Next &raquo;',
                        'active' => false,
                    ],
                ],
                'path' => route('events.index'),
                'per_page' => 15,
                'to' => null,
                'total' => 0,
            ],
        ]);
});

test('user can list paginated events', function () {
    Event::factory([
        'user_id' => $this->user->id,
    ])->count(25)->create();

    $this->getJson(
        route('events.index', ['user_id' => $this->user->id]) . '&per_page=10'
    )
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'user',
                    'title',
                    'description',
                    'start',
                    'end',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);
});

test('uses default pagination when no per_page parameter is provided', function () {
    Event::factory([
        'user_id' => $this->user->id,
    ])->count(20)->create();

    $response = $this->getJson(route('events.index', ['user_id' => $this->user->id]));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'user',
                    'title',
                    'description',
                    'start',
                    'end',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);
});

test('user gets validation error when only start date is provided', function () {
    $response = $this->getJson(route('events.index', ['user_id' => $this->user->id, 'start' => Carbon::now()->toDateTimeString()]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['end']);
});

test('user gets validation error when only end date is provided', function () {
    $response = $this->getJson(route('events.index', ['user_id' => $this->user->id, 'end' => Carbon::now()->addDay()->toDateTimeString()]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['start']);
});

test('user can not see other user events', function () {
    Event::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $anotherUser = User::factory()->create();

    $this->getJson(route('events.index', ['user_id' => $anotherUser->id]))
        ->assertStatus(200)
        ->assertJsonCount(0, 'data');
});
