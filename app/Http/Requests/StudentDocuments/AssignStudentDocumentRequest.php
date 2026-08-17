<?php

namespace App\Http\Requests\StudentDocuments;

use App\Models\StudentDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignStudentDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $document instanceof StudentDocument
            && ($this->user()?->can('assign', $document) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'assigned_secretary_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('is_active', true),
            ],
        ];
    }
}
