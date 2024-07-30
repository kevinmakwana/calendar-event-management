<?php

declare(strict_types=1);

namespace App\Event\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Event extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->user->name,
            'title' => $this->title,
            'description' => $this->description,
            'start' => $this->start,
            'end' => $this->end,
            'recurring_pattern' => $this->recurring_pattern,
            'frequency' => $this->frequency,
            'repeat_until' => $this->repeat_until,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
