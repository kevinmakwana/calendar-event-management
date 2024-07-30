<?php

declare(strict_types=1);

namespace App\User\Domain\Models;

use App\Event\Domain\Models\Event;
use App\User\Domain\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
    ];

    /**
     * @return UserFactory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /**
     * Get the events for the user.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
