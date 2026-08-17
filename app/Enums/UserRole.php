<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case SECRETARY = 'secretary';
    case EMPLOYEE = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::SECRETARY => 'Thư ký',
            self::EMPLOYEE => 'Nhân viên',
        };
    }
}
