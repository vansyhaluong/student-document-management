<?php

namespace Tests\Unit\Enums;

use App\Enums\StudentDocumentStatus;
use PHPUnit\Framework\TestCase;

class StudentDocumentStatusTest extends TestCase
{
    public function test_it_contains_only_the_approved_statuses(): void
    {
        $this->assertSame([
            'waiting_for_receipt',
            'received',
            'processing',
            'needs_supplement',
            'completed',
            'invalid',
            'cancelled',
        ], array_column(StudentDocumentStatus::cases(), 'value'));
    }

    public function test_it_enforces_the_approved_status_transitions(): void
    {
        $allowed = [
            StudentDocumentStatus::WAITING_FOR_RECEIPT->value => [
                StudentDocumentStatus::RECEIVED,
                StudentDocumentStatus::CANCELLED,
            ],
            StudentDocumentStatus::RECEIVED->value => [
                StudentDocumentStatus::PROCESSING,
                StudentDocumentStatus::INVALID,
                StudentDocumentStatus::CANCELLED,
            ],
            StudentDocumentStatus::PROCESSING->value => [
                StudentDocumentStatus::NEEDS_SUPPLEMENT,
                StudentDocumentStatus::COMPLETED,
                StudentDocumentStatus::INVALID,
                StudentDocumentStatus::CANCELLED,
            ],
            StudentDocumentStatus::NEEDS_SUPPLEMENT->value => [
                StudentDocumentStatus::PROCESSING,
                StudentDocumentStatus::CANCELLED,
            ],
            StudentDocumentStatus::COMPLETED->value => [],
            StudentDocumentStatus::INVALID->value => [],
            StudentDocumentStatus::CANCELLED->value => [],
        ];

        foreach (StudentDocumentStatus::cases() as $from) {
            foreach (StudentDocumentStatus::cases() as $to) {
                $this->assertSame(
                    in_array($to, $allowed[$from->value], true),
                    $from->canTransitionTo($to),
                    "{$from->value} -> {$to->value}",
                );
            }
        }
    }

    public function test_completed_invalid_and_cancelled_are_terminal(): void
    {
        $this->assertTrue(StudentDocumentStatus::COMPLETED->isTerminal());
        $this->assertTrue(StudentDocumentStatus::INVALID->isTerminal());
        $this->assertTrue(StudentDocumentStatus::CANCELLED->isTerminal());
        $this->assertFalse(StudentDocumentStatus::PROCESSING->isTerminal());
    }
}
