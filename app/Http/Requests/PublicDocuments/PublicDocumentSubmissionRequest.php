<?php

namespace App\Http\Requests\PublicDocuments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicDocumentSubmissionRequest extends FormRequest
{
    protected $errorBag = 'submission';

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'student_code' => ['required', 'string', 'max:20', 'exists:students,student_code'],
            'document_type_id' => [
                'required',
                'integer',
                Rule::exists('document_types', 'id')->where('is_active', true),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'student_code.required' => 'Vui lòng nhập mã sinh viên.',
            'student_code.max' => 'Mã sinh viên không được vượt quá 20 ký tự.',
            'student_code.exists' => 'Mã sinh viên không tồn tại trong hệ thống.',
            'document_type_id.required' => 'Vui lòng chọn loại hồ sơ.',
            'document_type_id.integer' => 'Loại hồ sơ không hợp lệ.',
            'document_type_id.exists' => 'Loại hồ sơ không khả dụng.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_code' => trim((string) $this->input('student_code')),
        ]);
    }
}
