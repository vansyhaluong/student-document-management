<?php

namespace App\Http\Requests\StudentDocuments;

use App\Enums\UserRole;
use App\Models\StudentDocument;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $document instanceof StudentDocument
            && ($this->user()?->can('update', $document) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        if ($this->user()?->hasRole(UserRole::EMPLOYEE)) {
            return ['note' => ['nullable', 'string', 'max:500']];
        }

        return [
            'student_code' => ['required', 'string', 'max:20', 'exists:students,student_code'],
            'document_type_id' => ['required', 'integer', 'exists:document_types,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = ['note' => $this->filled('note') ? trim((string) $this->input('note')) : null];

        if ($this->has('student_code')) {
            $data['student_code'] = trim((string) $this->input('student_code'));
        }

        $this->merge($data);
    }
}
