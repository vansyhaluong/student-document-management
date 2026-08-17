<?php

namespace App\Http\Requests\DocumentTypes;

use App\Models\DocumentType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('document_type');

        return $target instanceof DocumentType
            && ($this->user()?->can('update', $target) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên loại hồ sơ là bắt buộc.',
            'description.max' => 'Mô tả không được vượt quá 500 ký tự.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'description' => $this->filled('description')
                ? trim((string) $this->input('description'))
                : null,
        ]);
    }
}
