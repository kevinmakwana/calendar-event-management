<?php

declare(strict_types=1);

namespace App\Swagger\Components\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateEventRequest",
 *     type="object",
 *     title="Update Event Request",
 *     required={"title", "start", "end", "recurring_pattern"},
 *
 *     @OA\Property(property="title", type="string", description="Event title"),
 *     @OA\Property(property="description", type="string", description="Event description"),
 *     @OA\Property(property="start", type="string", format="date-time", description="Event start time"),
 *     @OA\Property(property="end", type="string", format="date-time", description="Event end time"),
 *     @OA\Property(property="recurring_pattern", type="boolean", description="Event is recurring"),
 *     @OA\Property(property="frequency", type="string", description="Event recurrence frequency", enum={"daily", "weekly", "monthly", "yearly"}),
 *     @OA\Property(property="repeat_until", type="string", format="date-time", description="Event recurrence end time"),
 * )
 */
class UpdateEventRequest
{
    // This class can be empty, it's just for the Swagger definition
}
