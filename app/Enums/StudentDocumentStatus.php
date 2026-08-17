<?php

namespace App\Enums;

enum StudentDocumentStatus: string
{
    case WAITING_FOR_RECEIPT = 'waiting_for_receipt';
    case RECEIVED = 'received';
    case PROCESSING = 'processing';
    case NEEDS_SUPPLEMENT = 'needs_supplement';
    case COMPLETED = 'completed';
    case INVALID = 'invalid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::WAITING_FOR_RECEIPT => 'Chờ tiếp nhận',
            self::RECEIVED => 'Đã tiếp nhận',
            self::PROCESSING => 'Đang xử lý',
            self::NEEDS_SUPPLEMENT => 'Cần bổ sung',
            self::COMPLETED => 'Hoàn tất',
            self::INVALID => 'Không hợp lệ',
            self::CANCELLED => 'Đã hủy',
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, match ($this) {
            self::WAITING_FOR_RECEIPT => [self::RECEIVED, self::CANCELLED],
            self::RECEIVED => [self::PROCESSING, self::INVALID, self::CANCELLED],
            self::PROCESSING => [
                self::NEEDS_SUPPLEMENT,
                self::COMPLETED,
                self::INVALID,
                self::CANCELLED,
            ],
            self::NEEDS_SUPPLEMENT => [self::PROCESSING, self::CANCELLED],
            self::COMPLETED, self::INVALID, self::CANCELLED => [],
        }, true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::INVALID, self::CANCELLED], true);
    }
}
