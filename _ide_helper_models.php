<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Event\Domain\Models{
/**
 * App\Event\Domain\Models\Event
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property \Carbon\Carbon $start
 * @property \Carbon\Carbon $end
 * @property string|null $recurring_pattern
 * @property string|null $frequency
 * @property string|null $repeat_until
 * @property int $user_id
 * @property int|null $parent_id
 * @property-read \App\User\Domain\Models\User $user
 * @mixin \Eloquent
 * @method static EventFactory factory(...$parameters)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRecurringPattern($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRepeatUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUserId($value)
 */
	class Event extends \Eloquent {}
}

namespace App\User\Domain\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Event\Domain\Models\Event> $events
 * @property-read int|null $events_count
 * @method static \App\User\Domain\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

