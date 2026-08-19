<?php

namespace App\DTOs;

use App\Enums\StudentDocumentStatus;

final readonly class StudentDocumentFilterData
{
    private const ALLOWED_SORTS = [
        'document_code',
        'status',
        'submitted_at',
        'updated_at',
    ];

    public function __construct(
        public ?string $keyword = null,
        public ?int $documentTypeId = null,
        public ?StudentDocumentStatus $status = null,
        public ?string $submittedFrom = null,
        public ?string $submittedTo = null,
        public string $sort = 'submitted_at',
        public string $direction = 'desc',
        public int $perPage = 15,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $status = $data['status'] ?? null;

        if (is_string($status) && $status !== '') {
            $status = StudentDocumentStatus::from($status);
        }

        $sort = in_array($data['sort'] ?? null, self::ALLOWED_SORTS, true)
            ? $data['sort']
            : 'submitted_at';
        $direction = ($data['direction'] ?? null) === 'asc' ? 'asc' : 'desc';
        $perPage = min(max((int) ($data['per_page'] ?? 15), 1), 100);

        return new self(
            keyword: self::nullableString($data['keyword'] ?? null),
            documentTypeId: self::nullableInt($data['document_type_id'] ?? null),
            status: $status instanceof StudentDocumentStatus ? $status : null,
            submittedFrom: self::nullableString($data['submitted_from'] ?? null),
            submittedTo: self::nullableString($data['submitted_to'] ?? null),
            sort: $sort,
            direction: $direction,
            perPage: $perPage,
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

    private static function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
