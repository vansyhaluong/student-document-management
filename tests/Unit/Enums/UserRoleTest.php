<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_role_values_match_the_approved_database_values(): void
    {
        $this->assertSame([
            'admin',
            'secretary',
            'staff',
        ], array_column(UserRole::cases(), 'value'));
    }
}
