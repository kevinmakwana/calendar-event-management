<?php

declare(strict_types=1);

namespace App\Event\Domain\Interfaces;

use App\Event\Domain\Models\Event;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface EventRepositoryInterface
{
    /**
     * Create a new event.
     *
     * @param array<string, mixed> $data
     * @return Event
     */
    public function create(array $data): Event;

    /**
     * Find an event by ID and user ID.
     *
     * @param int $id
     * @param int $userId
     * @return Event|null
     */
    public function findEventByIdAndUserId(int $id, int $userId): ?Event;

    /**
     * Update an existing event.
     *
     * @param Event $event
     * @param array<string, mixed> $data
     * @return bool
     */
    public function update(Event $event, array $data): bool;

    /**
     * Delete an event.
     *
     * @param Event $event
     * @return bool
     */
    public function delete(Event $event): bool;

    /**
     * Delete all subsequent events based on the parent ID.
     *
     * @param int $parentId
     * @return bool
     */
    public function deleteSubsequentEvents(int $parentId): bool;

    /**
     * Find events within a date range.
     *
     * @param string $start
     * @param string $end
     * @return Collection
     */
    public function findInRange(string $start, string $end): Collection;

    /**
     * Find events within a date range with pagination.
     *
     * @param string $start
     * @param string $end
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function findInRangePaginated(string $start, string $end, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find all events with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function findAllPaginated(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find recurring events based on the parent ID.
     *
     * @param int $parentId
     * @return Collection
     */
    public function findRecurringEvents(int $parentId): Collection;

    /**
     * Check for overlapping events within a given time range.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param int|null $excludeEventId
     * @return Event|null
     */
    public function checkOverlap(Carbon $start, Carbon $end, ?int $excludeEventId = null);

    /**
     * Get events in a date range with pagination for a user.
     *
     * @param int $userId
     * @param string $start
     * @param string $end
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserEventsInRangePaginated(int $userId, string $start, string $end, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all events with pagination for a user.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserAllEventsPaginated(int $userId, int $perPage = 15): LengthAwarePaginator;
}
