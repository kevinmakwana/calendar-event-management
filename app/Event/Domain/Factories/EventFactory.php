<?php

declare(strict_types=1);

namespace App\Event\Domain\Factories;

use App\Event\Domain\Models\Event;
use App\User\Domain\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            'user_id' => User::factory()->create()->id,
            'parent_id' => null,
            'title' => fake()->realText(20),
            'description' => fake()->paragraph(),
            'start' => Carbon::today(),
            'end' => Carbon::today(),
            'recurring_pattern' => false,
            'frequency' => null,
            'repeat_until' => null,
        ];
    }
}
