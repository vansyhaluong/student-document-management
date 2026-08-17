<?php

namespace App\Http\Requests\DocumentTypes;

use App\Models\DocumentType;
use Illuminate\Foundation\Http\FormRequest;

class ToggleDocumentTypeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('document_type');

        return $target instanceof DocumentType
            && ($this->user()?->can('update', $target) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
