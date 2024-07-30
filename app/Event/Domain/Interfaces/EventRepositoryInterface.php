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
     */
    public function create(array $data): Event;

    /**
     * Find an event by ID and user ID.
     */
    public function findUserEvent(int $id, int $userId): ?Event;

    /**
     * Update an existing event.
     *
     * @param array<string, mixed> $data
     */
    public function update(Event $event, array $data): bool;

    /**
     * Delete an event.
     */
    public function delete(Event $event): bool;

    /**
     * Delete all subsequent events based on the parent ID.
     */
    public function deleteSubsequentEvents(int $parentId): bool;

    /**
     * Find events within a date range.
     */
    public function findInRange(string $start, string $end): Collection;

    /**
     * Find events within a date range with pagination.
     */
    public function findInRangePaginated(string $start, string $end, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find all events with pagination.
     */
    public function findAllPaginated(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find recurring events based on the parent ID.
     */
    public function findRecurringEvents(int $parentId): Collection;

    /**
     * Check for overlapping events within a given time range.
     */
    public function checkOverlap(Carbon $start, Carbon $end, ?int $excludeEventId = null): ?Event;

    /**
     * Get events in a date range with pagination.
     */
    public function getUserEventsInRangePaginated(int $userId, string $start, string $end, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all events with pagination for a user.
     */
    public function getUserAllEventsPaginated(int $userId, int $perPage = 15): LengthAwarePaginator;
}
