<?php

declare(strict_types=1);

namespace App\Swagger\Components\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="EventCollection",
 *     type="object",
 *     title="Event Collection",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Event")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="total", type="integer", description="Total number of items"),
 *         @OA\Property(property="current_page", type="integer", description="Current page number"),
 *         @OA\Property(property="per_page", type="integer", description="Number of items per page"),
 *         @OA\Property(property="last_page", type="integer", description="Last page number")
 *     )
 * )
 */
class EventCollection
{
    // This class can be empty, it's just for the Swagger definition
}
