<?php

namespace App\Http\Requests;

use App\Models\EventAttendee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventAttendeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = (string) $this->route('event')?->getKey();

        return [
            'name' => ['required', 'string'],
            'email' => [
                'required',
                'email',
                Rule::unique('event_attendees', 'email')->where('event_id', $eventId),
            ],
            'attendance_status' => ['required', Rule::in(EventAttendee::STATUSES)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }
}
