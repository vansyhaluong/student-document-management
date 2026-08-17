<?php

namespace App\Http\Requests\DocumentTypes;

use App\DTOs\DocumentTypeFilterData;
use App\Models\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentTypeIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', DocumentType::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function filters(): DocumentTypeFilterData
    {
        return DocumentTypeFilterData::fromArray($this->validated());
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('keyword')) {
            $this->merge(['keyword' => trim((string) $this->input('keyword'))]);
        }
    }
}
