<?php

namespace App\DTOs;

use App\Enums\UserRole;

final readonly class UserFilterData
{
    public function __construct(
        public ?string $keyword = null,
        public ?UserRole $role = null,
        public ?bool $isActive = null,
        public int $perPage = 15,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $role = $data['role'] ?? null;

        return new self(
            keyword: self::nullableString($data['keyword'] ?? null),
            role: is_string($role) && $role !== '' ? UserRole::from($role) : null,
            isActive: self::nullableBoolean($data['is_active'] ?? null),
            perPage: min(max((int) ($data['per_page'] ?? 15), 1), 100),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private static function nullableBoolean(mixed $value): ?bool
    {
        return match ($value) {
            true, 1, '1' => true,
            false, 0, '0' => false,
            default => null,
        };
    }
}
