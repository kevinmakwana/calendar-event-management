<?php

declare(strict_types=1);

namespace App\Event\Application\Services;

use App\Event\Domain\Interfaces\EventRepositoryInterface;
use App\Event\Domain\Models\Event;
use Carbon\Carbon;
use DateInterval;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Service class to manage events.
 *
 * @property EventRepositoryInterface $repository
 */
class EventService
{
    /**
     * EventService constructor.
     */
    public function __construct(protected EventRepositoryInterface $repository) {}

    /**
     * Create a new event.
     *
     * @param array<string, mixed> $data
     *
     * @throws ValidationException
     * @throws InvalidArgumentException
     */
    public function createEvent(array $data): Event
    {
        $start = $this->convertToCarbon($data['start']);
        $end = $this->convertToCarbon($data['end']);

        // Validate the event duration
        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages([
                'end' => 'The end time must be a date after the start time.',
            ]);
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
            $initialEnd = $end; // Keep the initial end time for each occurrence
            $parentId = null;

            while (!$repeatUntil || $currentStart->lessThanOrEqualTo($repeatUntil)) {
                $this->validateOverlap($currentStart, $initialEnd);

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
        $this->validateOverlap($start, $end);

        return $this->repository->create($data);
    }

    /**
     * Get an event by ID and user ID.
     *
     * @throws ModelNotFoundException
     */
    public function getUserEventByIds(int $id, int $userId): Event
    {
        $event = $this->repository->findUserEvent($id, $userId);
        if (!$event) {
            throw new ModelNotFoundException('Event not found.');
        }

        return $event;
    }

    /**
     * Update an event.
     *
     * @param array<string, mixed> $data
     *
     * @throws ValidationException
     * @throws InvalidArgumentException
     */
    public function updateEvent(Event $event, array $data): bool
    {
        DB::transaction(function () use ($event, $data) {
            $start = $this->convertToCarbon($data['start']);
            $end = $this->convertToCarbon($data['end']);
            $repeatUntil = isset($data['repeat_until']) ? $this->convertToCarbon($data['repeat_until']) : $event->repeat_until;

            $this->validateOverlap($start, $end, $event->id);

            $recurringPattern = $data['recurring_pattern'] ?? $event->recurring_pattern;
            $frequency = $data['frequency'] ?? $event->recurring_pattern;

            $event->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? $event->description,
                'start' => $start,
                'end' => $end,
                'recurring_pattern' => $recurringPattern,
                'frequency' => $frequency,
                'repeat_until' => $repeatUntil,
            ]);

            // Update recurring events if applicable
            if ($recurringPattern) {
                $recurringEvents = Event::where('parent_id', $event->id)->orWhere('id', $event->id)->get();

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
        });

        return true;
    }

    /**
     * Delete an event.
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
     * Get events in a date range with pagination.
     */
    public function getUserEventsInRangePaginated(int $userId, string $start, string $end, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getUserEventsInRangePaginated($userId, $start, $end, $perPage);
    }

    /**
     * Get all events with pagination.
     */
    public function getUserAllEventsPaginated(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getUserAllEventsPaginated($userId, $perPage);
    }

    /**
     * Validate event overlap.
     *
     * @throws ValidationException
     * @throws InvalidArgumentException
     */
    protected function validateOverlap(Carbon|string $start, Carbon|string $end, ?int $excludeEventId = null): void
    {
        // Ensure $start and $end are Carbon instances
        $start = $this->convertToCarbon($start);
        $end = $this->convertToCarbon($end);

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
    }

    /**
     * Convert value to Carbon instance.
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
     * Validate recurrence details.
     *
     * @throws ValidationException
     */
    protected function validateRecurrence(Carbon $end, string $frequency, ?Carbon $repeatUntil): void
    {
        if (!in_array($frequency, ['daily', 'weekly', 'monthly', 'yearly'])) {
            throw ValidationException::withMessages([
                'frequency' => 'The frequency must be daily, weekly, monthly, or yearly.',
            ]);
        }

        if ($repeatUntil && $repeatUntil->lessThan($end)) {
            throw ValidationException::withMessages([
                'repeat_until' => 'The repeat_until date must be after the end date.',
            ]);
        }
    }

    /**
     * Get the next occurrence based on frequency.
     *
     * @throws InvalidArgumentException
     */
    private function getNextOccurrence(Carbon $currentStart, string $frequency): Carbon
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
                throw new InvalidArgumentException('Invalid occurrence frequency.');
        }
    }

    /**
     * Get the frequency interval.
     *
     * @throws InvalidArgumentException
     */
    private function getFrequencyInterval(string $frequency): DateInterval
    {
        switch ($frequency) {
            case 'daily':
                return new DateInterval('P1D'); // 1 day

            case 'weekly':
                return new DateInterval('P1W'); // 1 week

            case 'monthly':
                return new DateInterval('P1M'); // 1 month

            case 'yearly':
                return new DateInterval('P1Y'); // 1 year

            default:
                throw new InvalidArgumentException('Invalid frequency.');
        }
    }
}
