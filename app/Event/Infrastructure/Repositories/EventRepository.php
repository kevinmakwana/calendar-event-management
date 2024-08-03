<?php

declare(strict_types=1);

namespace App\Event\Infrastructure\Repositories;

use App\Event\Domain\Interfaces\EventRepositoryInterface;
use App\Event\Domain\Models\Event;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Validation\ValidationException;

class EventRepository implements EventRepositoryInterface
{
    /**
     * Create a new event.
     *
     * @param array<string, mixed> $data
     * @return Event
     */
    public function create(array $data): Event
    {
        try {
            return Event::create($data);
        } catch (Exception $e) {
            Log::error('Error creating event: ' . $e->getMessage(), ['data' => $data]);
            throw $e;
        }
    }

    /**
     * Find an event by ID and user ID.
     *
     * @param int $id
     * @param int $userId
     * @return Event|null
     */
    public function findEventByIdAndUserId(int $id, int $userId): ?Event
    {
        try {
            return Event::where('id', $id)
                ->where('user_id', $userId)
                ->first();
        } catch (Exception $e) {
            Log::error('Error finding event by ID and user ID: ' . $e->getMessage(), ['id' => $id, 'user_id' => $userId]);
            throw $e;
        }
    }

    /**
     * Update an existing event.
     *
     * @param Event $event
     * @param array<string, mixed> $data
     * @return bool
     */
    public function update(Event $event, array $data): bool
    {
        try {
            return $event->update($data);
        } catch (Exception $e) {
            Log::error('Error updating event: ' . $e->getMessage(), ['event_id' => $event->id, 'data' => $data]);
            throw $e;
        }
    }

    /**
     * Delete an event.
     *
     * @param Event $event
     * @return bool
     */
    public function delete(Event $event): bool
    {
        try {
            return $event->delete();
        } catch (Exception $e) {
            Log::error('Error deleting event: ' . $e->getMessage(), ['event_id' => $event->id]);
            throw $e;
        }
    }

    /**
     * Delete all subsequent events based on the parent ID.
     *
     * @param int $parentId
     * @return bool
     */
    public function deleteSubsequentEvents(int $parentId): bool
    {
        try {
            return DB::transaction(function () use ($parentId) {
                return Event::where('parent_id', $parentId)->delete();
            });
        } catch (Exception $e) {
            Log::error('Error deleting subsequent events: ' . $e->getMessage(), ['parent_id' => $parentId]);
            throw $e;
        }
    }

    /**
     * Find events within a date range.
     *
     * @param string $start
     * @param string $end
     * @return Collection
     */
    public function findInRange(string $start, string $end): Collection
    {
        try {
            return Event::whereBetween('start', [$start, $end])
                ->get();
        } catch (Exception $e) {
            Log::error('Error finding events in range: ' . $e->getMessage(), ['start' => $start, 'end' => $end]);
            throw $e;
        }
    }

    /**
     * Find events within a date range with pagination.
     *
     * @param string $start
     * @param string $end
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function findInRangePaginated(string $start, string $end, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Event::whereBetween('start', [$start, $end])
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error finding events in range with pagination: ' . $e->getMessage(), ['start' => $start, 'end' => $end, 'perPage' => $perPage]);
            throw $e;
        }
    }

    /**
     * Find all events with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function findAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Event::paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error finding all events with pagination: ' . $e->getMessage(), ['perPage' => $perPage]);
            throw $e;
        }
    }

    /**
     * Find recurring events based on the parent ID.
     *
     * @param int $parentId
     * @return Collection
     */
    public function findRecurringEvents(int $parentId): Collection
    {
        try {
            return Event::where('parent_id', $parentId)
                ->get();
        } catch (Exception $e) {
            Log::error('Error finding recurring events: ' . $e->getMessage(), ['parentId' => $parentId]);
            throw $e;
        }
    }

    /**
     * Check for overlapping events within a given time range.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param int|null $excludeEventId
     * @return Event|null
     */
    public function checkOverlap(Carbon $start, Carbon $end, ?int $excludeEventId = null)
    {
        try {
            $overlappingEvent = Event::where(function ($query) use ($start, $end) {
                $query->whereBetween('start', [$start, $end])
                    ->orWhereBetween('end', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->where('start', '<', $start)
                            ->where('end', '>', $end);
                    });
            })->when($excludeEventId, function ($query) use ($excludeEventId) {
                        $query->where('id', '!=', $excludeEventId);
            })->exists();

            if ($overlappingEvent) {
                throw ValidationException::withMessages([
                    'start' => 'The start time overlaps with another event.',
                    'end' => 'The end time overlaps with another event.',
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error checking event overlap: ' . $e->getMessage(), ['start' => $start, 'end' => $end, 'excludeEventId' => $excludeEventId]);
            throw $e;
        }
    }

    /**
     * Get events in a date range with pagination for a user.
     *
     * @param int $userId
     * @param string $start
     * @param string $end
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserEventsInRangePaginated(int $userId, string $start, string $end, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Event::where('user_id', $userId)
                ->whereBetween('start', [$start, $end])
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error getting user events in range with pagination: ' . $e->getMessage(), ['userId' => $userId, 'start' => $start, 'end' => $end, 'perPage' => $perPage]);
            throw $e;
        }
    }

    /**
     * Get all events with pagination for a user.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserAllEventsPaginated(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Event::where('user_id', $userId)
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error getting all user events with pagination: ' . $e->getMessage(), ['userId' => $userId, 'perPage' => $perPage]);
            throw $e;
        }
    }
}
