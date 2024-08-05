<?php

declare(strict_types=1);

namespace App\Event\Presentation\API\Controllers;

use App\Event\Application\Services\EventService;
use App\Event\Presentation\API\Requests\CreateEventRequest;
use App\Event\Presentation\API\Requests\IndexEventRequest;
use App\Event\Presentation\API\Requests\UpdateEventRequest;
use App\Event\Presentation\API\Resources\Event;
use App\Event\Presentation\API\Resources\EventCollection;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Info(
 *     title="Calendar Event Management API",
 *     version="1.0.0",
 *     description="API documentation for the Calendar Event Management system"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 */
class EventController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  EventService  $service  the service instance to handle event operations
     */
    public function __construct(protected EventService $service) {}

    /**
     * @OA\Get(
     *     path="/events",
     *     summary="Get a list of events within the specified range",
     *     tags={"Events"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/IndexEventRequest")
     *     ),
     *
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=true,
     *
     *         @OA\Schema(type="integer"),
     *         description="ID of the user"
     *     ),
     *
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *
     *         @OA\Schema(type="string", format="date-time"),
     *         description="Start date-time of the range"
     *     ),
     *
     *     @OA\Parameter(
     *         name="end",
     *         in="query",
     *
     *         @OA\Schema(type="string", format="date-time"),
     *         description="End date-time of the range"
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *
     *         @OA\Schema(type="integer"),
     *         description="Number of events per page"
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *
     *         @OA\JsonContent(ref="#/components/schemas/EventCollection")
     *     )
     * )
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
     * @OA\Post(
     *     path="/events",
     *     summary="Store a new event",
     *     tags={"Events"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/CreateEventRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Event created successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(CreateEventRequest $request): JsonResponse
    {
        try {
            $event = $this->service->createEvent($request->validated());

            return Response::apiResponse(new Event($event), 'Event created successfully.', 201);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/events/{id}",
     *     summary="Update an existing event",
     *     tags={"Events"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer"),
     *         description="ID of the event to update"
     *     ),
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         required=true,
     *
     *         @OA\Schema(type="integer"),
     *         description="ID of the user updating the event"
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateEventRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Event updated successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(UpdateEventRequest $request, int $id, int $user): JsonResponse
    {
        try {
            $event = $this->service->getUserEventByIds($id, $user);
            $this->service->updateEvent($event, $request->validated());

            return Response::apiResponse(new Event($event->fresh()), 'Event updated successfully.');
        } catch (ModelNotFoundException) {
            return Response::apiResponse(null, 'Event not found.', 404);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/events/{id}",
     *     summary="Delete an event",
     *     tags={"Events"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer"),
     *         description="ID of the event to delete"
     *     ),
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         required=true,
     *
     *         @OA\Schema(type="integer"),
     *         description="ID of the user deleting the event"
     *     ),
     *
     *     @OA\Parameter(
     *         name="deleteSubsequent",
     *         in="query",
     *
     *         @OA\Schema(type="boolean"),
     *         description="Whether to delete subsequent recurring events"
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Event deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, int $id, int $user): JsonResponse
    {
        $deleteSubsequent = filter_var($request->query('deleteSubsequent', 'false'), FILTER_VALIDATE_BOOLEAN);

        try {
            $event = $this->service->getUserEventByIds($id, $user);
            $this->service->deleteEvent($event, $deleteSubsequent);

            return Response::apiResponse(null, 'Event deleted successfully.', 204);
        } catch (ModelNotFoundException) {
            return Response::apiResponse(null, 'Event not found.', 404);
        } catch (Exception) {
            return Response::apiResponse(null, 'Bad request.', 400);
        }
    }

    /**
     * Handle validation exceptions.
     *
     * @param  ValidationException  $e  the validation exception
     * @return JsonResponse the response
     */
    private function handleValidationException(ValidationException $e): JsonResponse
    {
        return Response::apiResponse(
            null,
            $e->getMessage(),
            422,
            ['errors' => $e->errors()]
        );
    }
}
