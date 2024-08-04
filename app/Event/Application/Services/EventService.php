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
    public function __construct(private readonly EventRepositoryInterface $repository) {}

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
        $this->validateEventDuration($start, $end);

        // For non-recurring events
        $this->repository->checkOverlap($start, $end);
        $event = $this->repository->create($data);

        // Handle recurring events
        if (!empty($data['recurring_pattern'])) {
            $this->validateRecurrence($end, $data['frequency'], $this->convertToCarbon($data['repeat_until'] ?? null));
            $this->createRecurringEvents($event->id, $data, $start, $end);
        }

        return $event;
    }

    /**
     * Retrieve a user event by its ID and user ID.
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

            $event->update($this->getEventUpdateData($data, $start, $end, $repeatUntil));

            // Update recurring events if applicable
            if ($event->recurring_pattern) {
                $this->updateRecurringEvents($event->id, $data, $start, $end);
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
     * Validate event duration.
     *
     * @throws ValidationException
     */
    private function validateEventDuration(Carbon $start, Carbon $end): void
    {
        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages(['end' => 'The end time must be a date after the start time.']);
        }
    }

    /**
     * Validate recurrence parameters.
     *
     * @throws ValidationException
     */
    private function validateRecurrence(Carbon $end, string $frequency, ?Carbon $repeatUntil): void
    {
        if (!in_array($frequency, ['daily', 'weekly', 'monthly', 'yearly'])) {
            throw ValidationException::withMessages(['frequency' => 'The frequency must be daily, weekly, monthly, or yearly.']);
        }

        if ($repeatUntil && $repeatUntil->lessThan($end)) {
            throw ValidationException::withMessages(['repeat_until' => 'The repeat_until date must be after the end date.']);
        }
    }

    /**
     * Create recurring events based on the given data.
     */
    private function createRecurringEvents(int $eventId, array $data, Carbon $start, Carbon $end): void
    {
        // dd($data);
        $frequency = $data['frequency'];
        $repeatUntil = $this->convertToCarbon($data['repeat_until'] ?? null);
        $events = [];

        $currentStart = $start;
        $initialEnd = $end;
        // $parentId = null;

        while (!$repeatUntil || $currentStart->lessThanOrEqualTo($repeatUntil)) {
            $this->repository->checkOverlap($currentStart, $initialEnd, $eventId);

            $eventData = array_merge($data, [
                'start' => $currentStart,
                'end' => $initialEnd,
                'parent_id' => $eventId,
            ]);
            $event = $this->repository->create($eventData);
            $events[] = $event;

            $currentStart = $this->getNextOccurrence($currentStart, $frequency);
            $initialEnd = $initialEnd->copy()->add($this->getFrequencyInterval($frequency));
        }

        // return $events[0];
    }

    /**
     * Get the next occurrence date based on frequency.
     *
     * @throws InvalidArgumentException
     */
    private function getNextOccurrence(Carbon $currentStart, string $frequency): Carbon
    {
        return match ($frequency) {
            'daily' => $currentStart->addDay(),
            'weekly' => $currentStart->addWeek(),
            'monthly' => $currentStart->addMonth(),
            'yearly' => $currentStart->addYear(),
            default => throw new InvalidArgumentException("Invalid frequency: {$frequency}"),
        };
    }

    /**
     * Get the interval for the frequency.
     *
     * @throws InvalidArgumentException
     */
    private function getFrequencyInterval(string $frequency): CarbonInterval
    {
        return match ($frequency) {
            'daily' => CarbonInterval::day(),
            'weekly' => CarbonInterval::week(),
            'monthly' => CarbonInterval::month(),
            'yearly' => CarbonInterval::year(),
            default => throw new InvalidArgumentException("Invalid frequency: {$frequency}"),
        };
    }

    /**
     * Get the data for updating an event.
     */
    private function getEventUpdateData(array $data, Carbon $start, Carbon $end, ?Carbon $repeatUntil): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start' => $start,
            'end' => $end,
            'recurring_pattern' => $data['recurring_pattern'] ?? false,
            'frequency' => $data['frequency'] ?? null,
            'repeat_until' => $repeatUntil,
        ];
    }

    /**
     * Update recurring events based on the updated event data.
     */
    private function updateRecurringEvents(int $eventId, array $data, Carbon $start, Carbon $end): void
    {
        $recurringEvents = $this->repository->findRecurringEvents($eventId);

        foreach ($recurringEvents as $recurringEvent) {
            if ($recurringEvent->id !== $eventId) {
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
}
