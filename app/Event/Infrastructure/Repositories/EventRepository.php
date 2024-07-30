<?php

declare(strict_types=1);

namespace App\Event\Infrastructure\Repositories;

use App\Event\Domain\Interfaces\EventRepositoryInterface;
use App\Event\Domain\Models\Event;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EventRepository implements EventRepositoryInterface
{
    /**
     * Create a new event.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Event
    {
        return Event::create($data);
    }

    /**
     * Find an event by ID and user ID.
     */
    public function findUserEvent(int $id, int $userId): ?Event
    {
        return Event::where('id', $id)->where('user_id', $userId)->first();
    }

    /**
     * Update an existing event.
     *
     * @param array<string, mixed> $data
     */
    public function update(Event $event, array $data): bool
    {
        return $event->update($data);
    }

    /**
     * Delete an event.
     */
    public function delete(Event $event): bool
    {
        return $event->delete();
    }

    /**
     * Delete all subsequent events based on the parent ID.
     */
    public function deleteSubsequentEvents(int $parentId): bool
    {
        return Event::where('parent_id', $parentId)->delete();
    }

    /**
     * Find events within a date range.
     */
    public function findInRange(string $start, string $end): Collection
    {
        return Event::whereBetween('start', [$start, $end])->get();
    }

    /**
     * Find events within a date range with pagination.
     */
    public function findInRangePaginated(string $start, string $end, int $perPage = 15): LengthAwarePaginator
    {
        return Event::whereBetween('start', [$start, $end])->paginate($perPage);
    }

    /**
     * Find all events with pagination.
     */
    public function findAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Event::paginate($perPage);
    }

    /**
     * Find recurring events based on the parent ID.
     */
    public function findRecurringEvents(int $parentId): Collection
    {
        return Event::where('parent_id', $parentId)->get();
    }

    /**
     * Check for overlapping events within a given time range.
     */
    public function checkOverlap(Carbon $start, Carbon $end, ?int $excludeEventId = null): ?Event
    {
        $query = Event::where(function ($query) use ($start, $end) {
            $query->whereBetween('start', [$start, $end])
                ->orWhereBetween('end', [$start, $end])
                ->orWhere(function ($query) use ($start, $end) {
                    $query->where('start', '<', $start)
                        ->where('end', '>', $end);
                });
        });

        if ($excludeEventId) {
            $query->where('id', '<>', $excludeEventId);
        }

        return $query->first();
    }

    /**
     * Get events in a date range with pagination.
     */
    public function getUserEventsInRangePaginated(int $userId, string $start, string $end, int $perPage = 15): LengthAwarePaginator
    {
        return Event::where('user_id', $userId)->whereBetween('start', [$start, $end])->paginate($perPage);
    }

    /**
     * Get all events with pagination.
     */
    public function getUserAllEventsPaginated(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Event::where('user_id', $userId)->paginate($perPage);
    }
}
