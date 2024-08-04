<?php

namespace App\Swagger\Components\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Event",
 *     type="object",
 *     title="Event",
 *     @OA\Property(property="id", type="integer", description="Event ID"),
 *     @OA\Property(property="title", type="string", description="Event title"),
 *     @OA\Property(property="description", type="string", description="Event description"),
 *     @OA\Property(property="start", type="string", format="date-time", description="Event start time"),
 *     @OA\Property(property="end", type="string", format="date-time", description="Event end time"),
 *     @OA\Property(property="recurring_pattern", type="boolean", description="Event is recurring"),
 *     @OA\Property(property="frequency", type="string", description="Event recurrence frequency"),
 *     @OA\Property(property="repeat_until", type="string", format="date-time", description="Event recurrence end time")
 * )
 */
class Event
{
    // This class can be empty, it's just for the Swagger definition
}
