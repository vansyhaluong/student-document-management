<?php

namespace Tests\Unit\DTOs;

use App\DTOs\DocumentTypeFilterData;
use App\DTOs\UserFilterData;
use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class PhaseThreeFilterDataTest extends TestCase
{
    public function test_user_filters_map_approved_roles_and_status(): void
    {
        $filters = UserFilterData::fromArray([
            'keyword' => '  admin  ',
            'role' => 'staff',
            'is_active' => '0',
            'per_page' => 200,
        ]);

        $this->assertSame('admin', $filters->keyword);
        $this->assertSame(UserRole::EMPLOYEE, $filters->role);
        $this->assertFalse($filters->isActive);
        $this->assertSame(100, $filters->perPage);
    }

    public function test_document_type_filters_normalize_empty_values(): void
    {
        $filters = DocumentTypeFilterData::fromArray([
            'keyword' => '   ',
            'is_active' => '',
        ]);

        $this->assertNull($filters->keyword);
        $this->assertNull($filters->isActive);
        $this->assertSame(15, $filters->perPage);
    }
}
