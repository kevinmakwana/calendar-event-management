<?php

declare(strict_types=1);

namespace App\Event\Application\Services;

use App\Event\Domain\Interfaces\EventRepositoryInterface;
use App\Event\Domain\Models\Event;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class EventService
{
    public function __construct(protected EventRepositoryInterface $repository) {}

    /**
     * Create a new event, handling recurrence if applicable.
     *
     * @throws ValidationException
     */
    public function createEvent(array $data): Event
    {
        $start = $this->convertToCarbon($data['start']);
        $end = $this->convertToCarbon($data['end']);

        // Validate the event duration
        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages(['end' => 'The end time must be a date after the start time.']);
        }

        // Handle recurring events
        if (isset($data['recurring_pattern']) && $data['recurring_pattern']) {
            $frequency = $data['frequency'];
            $repeatUntil = $data['repeat_until'] ? $this->convertToCarbon($data['repeat_until']) : null;

            // Validate recurrence details
            $this->validateRecurrence($end, $frequency, $repeatUntil);

            // Generate recurring events
            $events = [];
            $currentStart = $start;
            $initialEnd = $end;
            $parentId = null;

            while (!$repeatUntil || $currentStart->lessThanOrEqualTo($repeatUntil)) {
                $this->repository->checkOverlap($currentStart, $initialEnd);

                $eventData = array_merge($data, [
                    'start' => $currentStart,
                    'end' => $initialEnd,
                    'parent_id' => $parentId,
                ]);

                $event = $this->repository->create($eventData);
                $events[] = $event;

                // Move to the next occurrence
                $currentStart = $this->getNextOccurrence($currentStart, $frequency);

                // Update end time based on frequency interval
                $initialEnd = $initialEnd->copy()->add($this->getFrequencyInterval($frequency));
            }

            // Return the first event as the reference
            return $events[0];
        }

        // For non-recurring events
        $this->repository->checkOverlap($start, $end);

        return $this->repository->create($data);
    }

    /**
     * Retrieve a user event by its ID and the user ID.
     *
     * @throws ModelNotFoundException
     */
    public function getUserEventByIds(int $id, int $userId): Event
    {
        $event = $this->repository->findEventByIdAndUserId($id, $userId);
        if (!$event) {
            throw new ModelNotFoundException('Event not found.');
        }

        return $event;
    }

    /**
     * Update an existing event and its recurring instances.
     */
    public function updateEvent(Event $event, array $data): bool
    {
        return DB::transaction(function () use ($event, $data) {
            $start = $this->convertToCarbon($data['start']);
            $end = $this->convertToCarbon($data['end']);
            $repeatUntil = $data['repeat_until'] ? $this->convertToCarbon($data['repeat_until']) : $event->repeat_until;

            $this->repository->checkOverlap($start, $end, $event->id);

            $event->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? $event->description,
                'start' => $start,
                'end' => $end,
                'recurring_pattern' => $data['recurring_pattern'] ?? $event->recurring_pattern,
                'frequency' => $data['frequency'] ?? $event->frequency,
                'repeat_until' => $repeatUntil,
            ]);

            // Update recurring events if applicable
            if ($event->recurring_pattern) {
                $recurringEvents = $this->repository->findRecurringEvents($event->id);

                foreach ($recurringEvents as $recurringEvent) {
                    if ($recurringEvent->id !== $event->id) {
                        $recurringEvent->update([
                            'title' => $data['title'],
                            'description' => $data['description'] ?? $recurringEvent->description,
                            'start' => $start,
                            'end' => $end,
                            'recurring_pattern' => false,
                            'frequency' => null,
                            'repeat_until' => null,
                        ]);
                    }
                }
            }

            return true;
        });
    }

    /**
     * Delete an event and optionally delete its subsequent occurrences.
     */
    public function deleteEvent(Event $event, bool $deleteSubsequent): void
    {
        DB::transaction(function () use ($event, $deleteSubsequent) {
            if ($deleteSubsequent) {
                $this->repository->deleteSubsequentEvents($event->id);
            }
            $event->delete();
        });
    }

    /**
     * Retrieve user events within a date range, paginated.
     */
    public function getUserEventsInRangePaginated(int $userId, string $start, string $end, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getUserEventsInRangePaginated($userId, $start, $end, $perPage);
    }

    /**
     * Retrieve all user events, paginated.
     */
    public function getUserAllEventsPaginated(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getUserAllEventsPaginated($userId, $perPage);
    }

    /**
     * Convert a value to a Carbon instance.
     *
     * @throws InvalidArgumentException
     */
    private function convertToCarbon(mixed $value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        if (is_string($value)) {
            return Carbon::parse($value);
        }

        throw new InvalidArgumentException('The value must be a string or an instance of Carbon.');
    }

    /**
     * Validate recurrence parameters.
     *
     * @param ?Carbon $repeatUntil
     * @throws ValidationException
     */
    protected function validateRecurrence(Carbon $end, string $frequency, ?Carbon $repeatUntil): void
    {
        if (!in_array($frequency, ['daily', 'weekly', 'monthly', 'yearly'])) {
            throw ValidationException::withMessages(['frequency' => 'The frequency must be daily, weekly, monthly, or yearly.']);
        }

        if ($repeatUntil && $repeatUntil->lessThan($end)) {
            throw ValidationException::withMessages(['repeat_until' => 'The repeat_until date must be after the end date.']);
        }
    }

    /**
     * Get the next occurrence date based on frequency.
     *
     * @throws InvalidArgumentException
     */
    public function getNextOccurrence(Carbon $currentStart, string $frequency): Carbon
    {
        switch ($frequency) {
            case 'daily':
                return $currentStart->addDay();

            case 'weekly':
                return $currentStart->addWeek();

            case 'monthly':
                return $currentStart->addMonth();

            case 'yearly':
                return $currentStart->addYear();

            default:
                throw new InvalidArgumentException("Invalid frequency: {$frequency}");
        }
    }

    /**
     * Get the interval for the frequency.
     *
     * @throws InvalidArgumentException
     */
    public function getFrequencyInterval(string $frequency): CarbonInterval
    {
        switch ($frequency) {
            case 'daily':
                return CarbonInterval::day();

            case 'weekly':
                return CarbonInterval::week();

            case 'monthly':
                return CarbonInterval::month();

            case 'yearly':
                return CarbonInterval::year();

            default:
                throw new InvalidArgumentException("Invalid frequency: {$frequency}");
        }
    }
}
