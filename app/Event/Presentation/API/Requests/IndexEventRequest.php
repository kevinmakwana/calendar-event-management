<?php

declare(strict_types=1);

namespace App\Event\Presentation\API\Requests;

use App\Event\Application\Services\EventService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read array<string, string> $rules
 */
class IndexEventRequest extends FormRequest
{
    protected EventService $eventService;

    public function __construct(EventService $eventService)
    {
        parent::__construct();
        $this->eventService = $eventService;
    }

    /**
     * Authorize the user to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define the validation rules for the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'start' => [
                'nullable',
                'date_format:Y-m-d H:i:s',
                'required_with:end',
            ],
            'end' => [
                'nullable',
                'required_with:start',
                'date_format:Y-m-d H:i:s',
            ],
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'start.date_format' => 'The start date must be a valid datetime.',
            'start.required_with' => 'The start date is required when the end date is present.',
            'end.required_with' => 'The end date is required when the start date is present.',
            'end.date_format' => 'The end date must be a valid datetime.',
        ];
    }
}
