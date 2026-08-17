<?php

namespace App\DTOs;

final readonly class DocumentTypeFilterData
{
    public function __construct(
        public ?string $keyword = null,
        public ?bool $isActive = null,
        public int $perPage = 15,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $keyword = is_string($data['keyword'] ?? null)
            ? trim($data['keyword'])
            : null;

        return new self(
            keyword: $keyword === '' ? null : $keyword,
            isActive: match ($data['is_active'] ?? null) {
                true, 1, '1' => true,
                false, 0, '0' => false,
                default => null,
            },
            perPage: min(max((int) ($data['per_page'] ?? 15), 1), 100),
        );
    }
}
