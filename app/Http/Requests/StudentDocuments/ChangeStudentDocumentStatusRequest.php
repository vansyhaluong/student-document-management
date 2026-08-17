<?php

namespace App\Http\Requests\StudentDocuments;

use App\Enums\StudentDocumentStatus;
use App\Models\StudentDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeStudentDocumentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $document instanceof StudentDocument
            && ($this->user()?->can('changeStatus', $document) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(StudentDocumentStatus::class)],
            'invalid_reason' => [
                Rule::requiredIf($this->input('status') === StudentDocumentStatus::INVALID->value),
                'nullable',
                'string',
                'max:200',
            ],
            'transition_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'invalid_reason' => $this->filled('invalid_reason')
                ? trim((string) $this->input('invalid_reason'))
                : null,
            'transition_note' => $this->filled('transition_note')
                ? trim((string) $this->input('transition_note'))
                : null,
        ]);
    }
}
