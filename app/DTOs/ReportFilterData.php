<?php

namespace App\DTOs;

use App\Enums\StudentDocumentStatus;

final readonly class ReportFilterData
{
    public function __construct(
        public ?int $documentTypeId = null,
        public ?StudentDocumentStatus $status = null,
        public ?string $submittedFrom = null,
        public ?string $submittedTo = null,
        public ?string $completedFrom = null,
        public ?string $completedTo = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $status = $data['status'] ?? null;

        return new self(
            documentTypeId: self::nullableInt($data['document_type_id'] ?? null),
            status: is_string($status) && $status !== ''
                ? StudentDocumentStatus::from($status)
                : null,
            submittedFrom: self::nullableString($data['submitted_from'] ?? null),
            submittedTo: self::nullableString($data['submitted_to'] ?? null),
            completedFrom: self::nullableString($data['completed_from'] ?? null),
            completedTo: self::nullableString($data['completed_to'] ?? null),
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
