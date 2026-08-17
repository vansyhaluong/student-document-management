<?php

namespace App\Http\Requests\PublicDocuments;

use Illuminate\Foundation\Http\FormRequest;

class PublicDocumentLookupRequest extends FormRequest
{
    protected $errorBag = 'lookup';

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'student_code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'student_code.required' => 'Vui lòng nhập mã số sinh viên.',
            'student_code.max' => 'Mã số sinh viên không được vượt quá 20 ký tự.',
            'student_code.regex' => 'Mã số sinh viên không đúng định dạng.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_code' => trim((string) $this->input('student_code')),
        ]);
    }
}
