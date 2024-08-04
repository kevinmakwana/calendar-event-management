<?php

namespace App\Swagger\Components\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CreateEventRequest",
 *     type="object",
 *     title="Create Event Request",
 *     required={"user_id", "title", "start", "end", "recurring_pattern"},
 *     @OA\Property(property="user_id", type="integer", description="User ID"),
 *     @OA\Property(property="title", type="string", description="Event title"),
 *     @OA\Property(property="description", type="string", description="Event description"),
 *     @OA\Property(property="start", type="string", format="date-time", description="Event start time"),
 *     @OA\Property(property="end", type="string", format="date-time", description="Event end time"),
 *     @OA\Property(property="recurring_pattern", type="boolean", description="Event is recurring"),
 *     @OA\Property(property="frequency", type="string", description="Event recurrence frequency", enum={"daily", "weekly", "monthly", "yearly"}),
 *     @OA\Property(property="repeat_until", type="string", format="date-time", description="Event recurrence end time"),
 * )
 */
class CreateEventRequest
{
    // This class can be empty, it's just for the Swagger definition
}
