<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case WaitingForReceipt = 'waiting_for_receipt';
    case Received = 'received';
    case Processing = 'processing';
    case NeedsSupplement = 'needs_supplement';
    case Completed = 'completed';
    case Invalid = 'invalid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::WaitingForReceipt => 'Chờ tiếp nhận',
            self::Received => 'Đã tiếp nhận',
            self::Processing => 'Đang xử lý',
            self::NeedsSupplement => 'Cần bổ sung',
            self::Completed => 'Hoàn tất',
            self::Invalid => 'Không hợp lệ',
            self::Cancelled => 'Đã hủy',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::WaitingForReceipt => 'badge-cho',
            self::Received => 'badge-tiep-nhan',
            self::Processing => 'badge-dang-xu-ly',
            self::NeedsSupplement => 'badge-bo-sung',
            self::Completed => 'badge-hoan-tat',
            self::Invalid => 'badge-khong-hop-le',
            self::Cancelled => 'badge-huy',
        };
    }
}
