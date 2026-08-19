<?php

namespace App\Http\Requests\StudentDocuments;

use App\Models\StudentDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', StudentDocument::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'document_code' => ['required', 'string', 'max:20', Rule::unique('student_documents', 'document_code')],
            'student_code' => ['required', 'string', 'max:20', 'exists:students,student_code'],
            'document_type_id' => [
                'required',
                'integer',
                Rule::exists('document_types', 'id')->where('is_active', true),
            ],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'document_code.required' => 'Mã hồ sơ là bắt buộc.',
            'document_code.unique' => 'Mã hồ sơ đã tồn tại.',
            'student_code.required' => 'Mã sinh viên là bắt buộc.',
            'student_code.exists' => 'Mã sinh viên không tồn tại trong hệ thống.',
            'document_type_id.required' => 'Loại hồ sơ là bắt buộc.',
            'document_type_id.exists' => 'Loại hồ sơ không khả dụng.',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'document_code' => trim((string) $this->input('document_code')),
            'student_code' => trim((string) $this->input('student_code')),
            'note' => $this->filled('note') ? trim((string) $this->input('note')) : null,
        ]);
    }
}
