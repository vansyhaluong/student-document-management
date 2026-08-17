<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ListPublicStudentDocumentsRequest extends FormRequest
{
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

    public function studentCode(): string
    {
        return (string) $this->validated('student_code');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_code' => trim((string) $this->route('studentCode')),
        ]);
    }
}
