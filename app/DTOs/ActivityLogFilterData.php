<?php

namespace App\DTOs;

final readonly class ActivityLogFilterData
{
    public function __construct(
        public ?string $event = null,
        public ?int $actorUserId = null,
        public ?string $subjectType = null,
        public ?int $subjectId = null,
        public ?string $from = null,
        public ?string $to = null,
        public int $perPage = 20,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            event: self::nullableString($data['event'] ?? null),
            actorUserId: self::nullableInt($data['actor_user_id'] ?? null),
            subjectType: self::nullableString($data['subject_type'] ?? null),
            subjectId: self::nullableInt($data['subject_id'] ?? null),
            from: self::nullableString($data['from'] ?? null),
            to: self::nullableString($data['to'] ?? null),
            perPage: min(max((int) ($data['per_page'] ?? 20), 1), 100),
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
