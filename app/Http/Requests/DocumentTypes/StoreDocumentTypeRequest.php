<?php

namespace App\Http\Requests\DocumentTypes;

use App\Models\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DocumentType::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('document_types', 'code')],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Mã loại hồ sơ là bắt buộc.',
            'code.unique' => 'Mã loại hồ sơ đã tồn tại.',
            'name.required' => 'Tên loại hồ sơ là bắt buộc.',
            'description.max' => 'Mô tả không được vượt quá 500 ký tự.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => trim((string) $this->input('code')),
            'name' => trim((string) $this->input('name')),
            'description' => $this->filled('description')
                ? trim((string) $this->input('description'))
                : null,
        ]);
    }
}
