<?php

declare(strict_types=1);

namespace App\Event\Infrastructure\Repositories;

use App\Event\Domain\Interfaces\EventRepositoryInterface;
use App\Event\Domain\Models\Event;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventRepository implements EventRepositoryInterface
{
    /**
     * Create a new event.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Event
    {
        try {
            return Event::create($data);
        } catch (QueryException $e) {
            Log::error('Database error creating event: ' . $e->getMessage(), ['data' => $data]);

            throw $e;
        } catch (Exception $e) {
            Log::error('Error creating event: ' . $e->getMessage(), ['data' => $data]);

            throw $e;
        }
    }

    /**
     * Find an event by ID and user ID.
     *
     * @throws ModelNotFoundException
     */
    public function findEventByIdAndUserId(int $id, int $userId): Event
    {
        return Event::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    /**
     * Update an existing event.
     *
     * @param array<string, mixed> $data
     */
    public function update(Event $event, array $data): bool
    {
        try {
            return $event->update($data);
        } catch (QueryException $e) {
            Log::error('Database error updating event: ' . $e->getMessage(), ['event_id' => $event->id, 'data' => $data]);

            throw $e;
        } catch (Exception $e) {
            Log::error('Error updating event: ' . $e->getMessage(), ['event_id' => $event->id, 'data' => $data]);

            throw $e;
        }
    }

    /**
     * Delete an event.
     */
    public function delete(Event $event): bool
    {
        try {
            return $event->delete();
        } catch (QueryException $e) {
            Log::error('Database error deleting event: ' . $e->getMessage(), ['event_id' => $event->id]);

            throw $e;
        } catch (Exception $e) {
            Log::error('Error deleting event: ' . $e->getMessage(), ['event_id' => $event->id]);

            throw $e;
        }
    }

    /**
     * Delete all subsequent events based on the parent ID.
     */
    public function deleteSubsequentEvents(int $parentId): bool
    {
        try {
            return DB::transaction(fn() => Event::where('parent_id', $parentId)->delete() > 0);
        } catch (QueryException $e) {
            Log::error('Database error deleting subsequent events: ' . $e->getMessage(), ['parent_id' => $parentId]);

            throw $e;
        } catch (Exception $e) {
            Log::error('Error deleting subsequent events: ' . $e->getMessage(), ['parent_id' => $parentId]);

            throw $e;
        }
    }

    /**
     * Find events within a date range.
     */
    public function findInRange(string $start, string $end): Collection
    {
        try {
            return Event::whereBetween('start', [$start, $end])->get();
        } catch (QueryException $e) {
            Log::error('Database error finding events in range: ' . $e->getMessage(), ['start' => $start, 'end' => $end]);

            throw $e;
        } catch (Exception $e) {
            Log::error('Error finding events in range: ' . $e->getMessage(), ['start' => $start, 'end' => $end]);

            throw $e;
        }
    }

    /**
     * Find events within a date range with pagination.
     */
    public function findInRangePaginated(string $start, string $end, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Event::whereBetween('start', [$start, $end])->paginate($perPage);
        } catch (QueryException $e) {
            Log::error('Database error finding events in range with pagination: ' . $e->getMessage(), ['start' => $start, 'end' => $end, 'perPage' => $perPage]);

            throw $e;
        } catch (Exception $e) {
            Log::error('Error finding events in range with pagination: ' . $e->getMessage(), ['start' => $start, 'end' => $end, 'perPage' => $perPage]);

            throw $e;
        }
    }

    /**
     * Find all events with pagination.
     */
    public function findAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Event::paginate($perPage);
        } catch (QueryException $e) {
            Log::error('Database error finding all events with pagination: ' . $e->getMessage(), ['perPage' => $perPage]);

            throw $e;
        } catch (Exception $e) {
            Log::error('Error finding all events with pagination: ' . $e->getMessage(), ['perPage' => $perPage]);

            throw $e;
        }
    }

    /**
     * Find recurring events based on the parent ID.
     */
    public function findRecurringEvents(int $parentId): Collection
    {
        try {
            return Event::where('parent_id', $parentId)->get();
        } catch (QueryException $e) {
            Log::error('Database error finding recurring events: ' . $e->getMessage(), ['parentId' => $parentId]);

            throw $e;
        } catch (Exception $e) {
            Log::error('Error finding recurring events: ' . $e->getMessage(), ['parentId' => $parentId]);

            throw $e;
        }
    }

    /**
     * Check for overlapping events within a given time range.
     *
     * @throws HttpResponseException
     */
    public function checkOverlap(Carbon $start, Carbon $end, ?int $excludeEventId = null): void
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
            $query->where('id', '!=', $excludeEventId);
        }

        $overlappingEvents = $query->get();

        if ($overlappingEvents->count() > 0) {
            foreach ($overlappingEvents as $overlappingEvent) {
                if ($this->isOverlapping($start, $end, $overlappingEvent)) {
                    $this->handleOverlap($overlappingEvent);
                }
            }
        }
    }

    /**
     * Check if the new event overlaps with an existing event's occurrences.
     */
    private function isOverlapping(Carbon $start, Carbon $end, Event $existingEvent): bool
    {
        return $start->lessThanOrEqualTo($existingEvent->end)
            && $end->greaterThanOrEqualTo($existingEvent->start);
    }

    /**
     * Handle the overlap scenario by throwing an exception with appropriate messages.
     *
     * @throws HttpResponseException
     */
    private function handleOverlap(Event $overlappingEvent): void
    {
        $message = 'The start time & end time overlaps with another event.';
        $errors = [
            'start' => ['The start time overlaps with another event.'],
            'end' => ['The end time overlaps with another event.'],
        ];

        if ($overlappingEvent->recurring_pattern) {
            $message = 'The start time & end time overlaps with a recurring event.';
            $errors = [
                'start' => ['The start time overlaps with a recurring event.'],
                'end' => ['The end time overlaps with a recurring event.'],
            ];
        }

        throw new HttpResponseException(response()->json([
            'message' => $message,
            'errors' => $errors,
        ], 422));
    }

    /**
     * Get events in a date range with pagination for a user.
     */
    public function getUserEventsInRangePaginated(int $userId, string $start, string $end, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Event::where('user_id', $userId)
                ->whereBetween('start', [$start, $end])
                ->paginate($perPage);
        } catch (QueryException $e) {
            Log::error('Database error getting user events in range with pagination: ' . $e->getMessage(), ['userId' => $userId, 'start' => $start, 'end' => $end, 'perPage' => $perPage]);

            throw $e;
        } catch (Exception $e) {
            Log::error('Error getting user events in range with pagination: ' . $e->getMessage(), ['userId' => $userId, 'start' => $start, 'end' => $end, 'perPage' => $perPage]);

            throw $e;
        }
    }

    /**
     * Get all events with pagination for a user.
     */
    public function getUserAllEventsPaginated(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Event::where('user_id', $userId)
                ->paginate($perPage);
        } catch (QueryException $e) {
            Log::error('Database error getting all user events with pagination: ' . $e->getMessage(), ['userId' => $userId, 'perPage' => $perPage]);

            throw $e;
        } catch (Exception $e) {
            Log::error('Error getting all user events with pagination: ' . $e->getMessage(), ['userId' => $userId, 'perPage' => $perPage]);

            throw $e;
        }
    }
}
