<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'location' => 'required|string|max:255',
            'talks' => 'array',
            'talks.*.id' => 'nullable|integer|exists:talks,id',
            'talks.*.title' => 'required_with:talks|string|max:255',
            'talks.*.description' => 'required_with:talks|string',
            'talks.*.speaker_name' => 'required_with:talks|string|max:255',
            'talks.*.start_time' => 'required_with:talks|date|after_or_equal:start_datetime|before:end_datetime',
            'talks.*.end_time' => 'required_with:talks|date|after:talks.*.start_time|before_or_equal:end_datetime',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Event title is required.',
            'description.required' => 'Event description is required.',
            'start_datetime.required' => 'Start date and time is required.',
            'end_datetime.required' => 'End date and time is required.',
            'end_datetime.after' => 'End date and time must be after start date and time.',
            'location.required' => 'Event location is required.',
            'talks.*.title.required_with' => 'Talk title is required when adding talks.',
            'talks.*.description.required_with' => 'Talk description is required when adding talks.',
            'talks.*.speaker_name.required_with' => 'Speaker name is required when adding talks.',
            'talks.*.start_time.required_with' => 'Talk start time is required when adding talks.',
            'talks.*.start_time.after_or_equal' => 'Talk start time must be after or equal to event start time.',
            'talks.*.start_time.before' => 'Talk start time must be before event end time.',
            'talks.*.end_time.required_with' => 'Talk end time is required when adding talks.',
            'talks.*.end_time.after' => 'Talk end time must be after talk start time.',
            'talks.*.end_time.before_or_equal' => 'Talk end time must be before or equal to event end time.',
        ];
    }
}
