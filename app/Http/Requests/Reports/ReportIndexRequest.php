<?php

namespace App\Http\Requests\Reports;

use App\DTOs\ReportFilterData;
use App\Enums\StudentDocumentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view-reports') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'document_type_id' => ['nullable', 'integer', 'exists:document_types,id'],
            'status' => ['nullable', Rule::enum(StudentDocumentStatus::class)],
            'submitted_from' => ['nullable', 'date_format:Y-m-d'],
            'submitted_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:submitted_from'],
            'completed_from' => ['nullable', 'date_format:Y-m-d'],
            'completed_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:completed_from'],
        ];
    }

    public function filters(): ReportFilterData
    {
        return ReportFilterData::fromArray($this->validated());
    }
}
