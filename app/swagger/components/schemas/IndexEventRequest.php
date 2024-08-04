<?php

declare(strict_types=1);

namespace App\Swagger\Components\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="IndexEventRequest",
 *     type="object",
 *     title="Index Event Request",
 *     required={"user_id"},
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="The ID of the user",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="start",
 *         type="string",
 *         format="date-time",
 *         description="The start datetime for filtering events",
 *         example="2024-08-01T10:00:00"
 *     ),
 *     @OA\Property(
 *         property="end",
 *         type="string",
 *         format="date-time",
 *         description="The end datetime for filtering events",
 *         example="2024-08-01T12:00:00"
 *     ),
 *     @OA\Property(
 *         property="page",
 *         type="integer",
 *         description="The page number for pagination",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="per_page",
 *         type="integer",
 *         description="The number of items per page for pagination",
 *         example=10
 *     )
 * )
 */
class IndexEventRequest
{
    // This class can be empty, it's just for the Swagger definition
}
