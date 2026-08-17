<?php

namespace App\Http\Requests\StudentDocuments;

use App\DTOs\StudentDocumentFilterData;
use App\Enums\StudentDocumentStatus;
use App\Models\StudentDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentDocumentIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', StudentDocument::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:150'],
            'document_type_id' => ['nullable', 'integer', 'exists:document_types,id'],
            'status' => ['nullable', Rule::enum(StudentDocumentStatus::class)],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'submitted_from' => ['nullable', 'date_format:Y-m-d'],
            'submitted_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:submitted_from'],
            'sort' => ['nullable', Rule::in(['document_code', 'status', 'submitted_at', 'updated_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function filters(): StudentDocumentFilterData
    {
        return StudentDocumentFilterData::fromArray($this->validated());
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('keyword')) {
            $this->merge(['keyword' => trim((string) $this->input('keyword'))]);
        }
    }
}
