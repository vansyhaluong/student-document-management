<?php

namespace App\Http\Requests\Users;

use App\DTOs\UserFilterData;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:150'],
            'role' => ['nullable', Rule::enum(UserRole::class)],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function filters(): UserFilterData
    {
        return UserFilterData::fromArray($this->validated());
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('keyword')) {
            $this->merge(['keyword' => trim((string) $this->input('keyword'))]);
        }
    }
}
