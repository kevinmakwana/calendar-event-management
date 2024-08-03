<?php

declare(strict_types=1);

namespace App\Event\Presentation\API\Controllers;

use App\Event\Application\Services\EventService;
use App\Event\Presentation\API\Requests\CreateEventRequest;
use App\Event\Presentation\API\Requests\IndexEventRequest;
use App\Event\Presentation\API\Requests\UpdateEventRequest;
use App\Event\Presentation\API\Resources\Event;
use App\Event\Presentation\API\Resources\EventCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class EventController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param EventService $service the service instance to handle event operations
     */
    public function __construct(protected EventService $service) {}

    /**
     * Get a list of events within the specified range.
     *
     * @param IndexEventRequest $request the HTTP request instance
     * @return EventCollection a JSON response containing the list of events
     */
    public function index(IndexEventRequest $request): EventCollection
    {
        $validated = $request->validated();
        $userId = (int) $validated['user_id'];
        $perPage = (int) $request->input('per_page', 15);

        $events = isset($validated['start'], $validated['end'])
            ? $this->service->getUserEventsInRangePaginated($userId, $validated['start'], $validated['end'], $perPage)
            : $this->service->getUserAllEventsPaginated($userId, $perPage);

        return new EventCollection($events);
    }

    /**
     * Store a new event.
     *
     * @param CreateEventRequest $request the request instance containing event data
     * @return JsonResponse a JSON response containing the created event
     */
    public function store(CreateEventRequest $request): JsonResponse
    {
        $event = $this->service->createEvent($request->validated());
        return Response::apiResponse(new Event($event), 'Event created successfully.', 201);
    }

    /**
     * Update an existing event.
     *
     * @param UpdateEventRequest $request the request instance containing updated event data
     * @param int $id the ID of the event to update
     * @param int $user the ID of the user updating the event
     * @return JsonResponse a JSON response containing the updated event
     */
    public function update(UpdateEventRequest $request, int $id, int $user): JsonResponse
    {
        try {
            $event = $this->service->getUserEventByIds($id, $user);
            $this->service->updateEvent($event, $request->validated());
            return Response::apiResponse(new Event($event->fresh()), 'Event updated successfully.');
        } catch (ModelNotFoundException $e) {
            Log::error('Event not found: ', ['error' => $e->getMessage()]);
            return Response::apiResponse(null, 'Event not found.', 404);
        } catch (\Exception $e) {
            Log::error('Error updating event: ', ['error' => $e->getMessage()]);
            return Response::apiResponse(null, $e->getMessage(), 400);
        }
    }

    /**
     * Delete an event.
     *
     * @param Request $request the HTTP request instance
     * @param int $id the ID of the event to delete
     * @param int $user the ID of the user deleting the event
     * @return JsonResponse a JSON response indicating successful deletion
     */
    public function destroy(Request $request, int $id, int $user): JsonResponse
    {
        $deleteSubsequent = $request->query('deleteSubsequent', 'false') === 'true';

        try {
            $event = $this->service->getUserEventByIds($id, $user);
            $this->service->deleteEvent($event, $deleteSubsequent);
            return Response::apiResponse(null, 'Event deleted successfully.', 204);
        } catch (ModelNotFoundException $e) {
            Log::error('Event not found: ', ['error' => $e->getMessage()]);
            return Response::apiResponse(null, $e->getMessage(), 404);
        } catch (\Exception $e) {
            Log::error('Error deleting event: ', ['error' => $e->getMessage()]);
            return Response::apiResponse(null, $e->getMessage(), 400);
        }
    }
}
