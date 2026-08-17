<?php

namespace App\Http\Requests\ActivityLogs;

use App\DTOs\ActivityLogFilterData;
use Illuminate\Foundation\Http\FormRequest;

class ActivityLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view-activity-log') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'event' => ['nullable', 'string', 'max:100'],
            'actor_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'subject_type' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function filters(): ActivityLogFilterData
    {
        return ActivityLogFilterData::fromArray($this->validated());
    }

    protected function prepareForValidation(): void
    {
        foreach (['event', 'subject_type'] as $field) {
            if ($this->has($field)) {
                $this->merge([$field => trim((string) $this->input($field))]);
            }
        }
    }
}
