<?php

declare(strict_types=1);

namespace App\Event\Domain\Models;

use App\Event\Domain\Factories\EventFactory;
use App\User\Domain\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 *
 * @mixin \Eloquent
 *
 * @method static EventFactory factory(...$parameters)
 */
class Event extends Model
{
    use HasFactory;

    /**
     * The relationships that should be eager loaded on every query.
     *
     * @var array<int, string>
     */
    protected $with = ['user'];

    /**
     * Mass assignable columns.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'parent_id',
        'title',
        'description',
        'start',
        'end',
        'recurring_pattern',
        'frequency',
        'repeat_until',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'recurring_pattern' => 'boolean',
    ];

    /**
     * @return EventFactory
     */
    protected static function newFactory()
    {
        return EventFactory::new();
    }

    /**
     * Get and set methods for the start attribute.
     */
    protected function start(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->toIso8601String(),
            set: fn(string $value) => Carbon::parse($value)->toIso8601String(),
        );
    }

    /**
     * Get and set methods for the end attribute.
     */
    protected function end(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->toIso8601String(),
            set: fn(string $value) => Carbon::parse($value)->toIso8601String(),
        );
    }

    /**
     * Get and set methods for the repeat_until attribute.
     */
    protected function repeatUntil(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? Carbon::parse($value)->toIso8601String() : null,
            set: fn(?string $value) => $value ? Carbon::parse($value)->toIso8601String() : null,
        );
    }

    /**
     * Get the user that owns the event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
