<?php

namespace Tests\Unit\DTOs;

use App\DTOs\StudentDocumentFilterData;
use App\Enums\StudentDocumentStatus;
use PHPUnit\Framework\TestCase;

class StudentDocumentFilterDataTest extends TestCase
{
    public function test_it_normalizes_supported_filters(): void
    {
        $filters = StudentDocumentFilterData::fromArray([
            'keyword' => '  HS-001  ',
            'document_type_id' => '2',
            'status' => 'processing',
            'responsible_user_id' => '3',
            'submitted_from' => '2026-01-01',
            'submitted_to' => '2026-01-31',
            'sort' => 'updated_at',
            'direction' => 'asc',
            'per_page' => '25',
        ]);

        $this->assertSame('HS-001', $filters->keyword);
        $this->assertSame(2, $filters->documentTypeId);
        $this->assertSame(StudentDocumentStatus::PROCESSING, $filters->status);
        $this->assertSame(3, $filters->responsibleUserId);
        $this->assertSame('2026-01-01', $filters->submittedFrom);
        $this->assertSame('2026-01-31', $filters->submittedTo);
        $this->assertSame('updated_at', $filters->sort);
        $this->assertSame('asc', $filters->direction);
        $this->assertSame(25, $filters->perPage);
    }

    public function test_it_uses_safe_defaults_for_sorting_and_page_size(): void
    {
        $filters = StudentDocumentFilterData::fromArray([
            'sort' => 'unexpected_column',
            'direction' => 'unexpected_direction',
            'per_page' => 1000,
        ]);

        $this->assertSame('submitted_at', $filters->sort);
        $this->assertSame('desc', $filters->direction);
        $this->assertSame(100, $filters->perPage);
    }
}
