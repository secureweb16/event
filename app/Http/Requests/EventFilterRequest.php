<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class EventFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(Event::STATUSES)],
            'location' => ['nullable', 'string'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'sort' => ['nullable', Rule::in(['newest', 'upcoming'])],
        ];
    }

    /**
     * @return array{status: string|null, location: string|null, from: string|null, to: string|null, sort: string}
     */
    public function filters(?string $defaultFrom = null): array
    {
        $validated = $this->validated();

        return [
            'status' => $validated['status'] ?? null,
            'location' => $validated['location'] ?? null,
            'from' => $validated['from'] ?? $defaultFrom,
            'to' => $validated['to'] ?? null,
            'sort' => $validated['sort'] ?? 'upcoming',
        ];
    }

    public function sort(): string
    {
        return $this->validated()['sort'] ?? 'newest';
    }

    public function perPage(int $default): int
    {
        return (int) ($this->validated()['per_page'] ?? $default);
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->routeIs('events.data')) {
            throw new HttpResponseException(response()->json([
                'message' => 'The event filters are invalid.',
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
