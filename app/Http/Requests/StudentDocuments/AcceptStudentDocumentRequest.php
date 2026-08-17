<?php

namespace App\Http\Requests\StudentDocuments;

use App\Models\StudentDocument;
use Illuminate\Foundation\Http\FormRequest;

class AcceptStudentDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $document instanceof StudentDocument
            && ($this->user()?->can('accept', $document) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['transition_note' => ['nullable', 'string', 'max:500']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'transition_note' => $this->filled('transition_note')
                ? trim((string) $this->input('transition_note'))
                : null,
        ]);
    }
}
