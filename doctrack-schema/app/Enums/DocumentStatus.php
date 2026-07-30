<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case WAITING_FOR_RECEIPT = 'waiting_for_receipt'; // Chờ tiếp nhận
    case RECEIVED = 'received';                       // Đã tiếp nhận
    case PROCESSING = 'processing';                   // Đang xử lý
    case NEEDS_SUPPLEMENT = 'needs_supplement';        // Cần bổ sung
    case COMPLETED = 'completed';                      // Đã hoàn thành
    case INVALID = 'invalid';                           // Không hợp lệ
    case CANCELLED = 'cancelled';                       // Đã hủy

    public function label(): string
    {
        return match ($this) {
            self::WAITING_FOR_RECEIPT => 'Chờ tiếp nhận',
            self::RECEIVED => 'Đã tiếp nhận',
            self::PROCESSING => 'Đang xử lý',
            self::NEEDS_SUPPLEMENT => 'Cần bổ sung',
            self::COMPLETED => 'Đã hoàn thành',
            self::INVALID => 'Không hợp lệ',
            self::CANCELLED => 'Đã hủy',
        };
    }
}
