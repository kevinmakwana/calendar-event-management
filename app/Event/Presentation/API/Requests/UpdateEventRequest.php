<?php

declare(strict_types=1);

namespace App\Event\Presentation\API\Requests;

use App\Event\Application\Services\EventService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property array<string, string> $rules
 */
class UpdateEventRequest extends FormRequest
{
    public function __construct(protected EventService $eventService)
    {
        parent::__construct();
    }

    /**
     * Define the validation rules for the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'sometimes|required|string|max:1000',
            'start' => [
                'required',
                'date_format:Y-m-d\TH:i:sP',
                'after_or_equal:now',
            ],
            'end' => [
                'required',
                'date_format:Y-m-d\TH:i:sP',
                'after:start',
            ],
            'recurring_pattern' => 'required|boolean',
            'frequency' => [
                'nullable',
                Rule::requiredIf($this->input('recurring_pattern') === true),
                'string',
                'in:daily,weekly,monthly,yearly',
            ],
            'repeat_until' => [
                'nullable',
                Rule::requiredIf($this->input('recurring_pattern') === true),
                'date_format:Y-m-d\TH:i:sP',
                'after:end',
            ],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages()
    {
        return [
            'title.required' => 'The title is required.',
            'start.required' => 'The start date is required.',
            'end.required' => 'The end date is required.',
            'recurring_pattern.required' => 'The recurring pattern is required.',
            'recurring_pattern.boolean' => 'The recurring pattern must be true or false.',
            'frequency.required_if' => 'The frequency is required when recurring pattern is true.',
            'repeat_until.required_if' => 'The repeat until date is required when recurring pattern is true.',
        ];
    }

    /**
     * Authorize the user to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
