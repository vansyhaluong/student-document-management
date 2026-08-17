<?php

namespace Tests\Unit\Models;

use App\Models\ActivityLog;
use App\Models\DocumentStatusHistory;
use App\Models\DocumentType;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class SchemaMappingTest extends TestCase
{
    public function test_user_authentication_uses_the_existing_password_hash_column(): void
    {
        $user = new User;

        $this->assertSame('password_hash', $user->getAuthPasswordName());
        $this->assertContains('password_hash', $user->getHidden());
    }

    public function test_models_follow_the_existing_primary_keys_and_timestamp_columns(): void
    {
        $student = new Student;
        $document = new StudentDocument;

        $this->assertSame('student_code', $student->getKeyName());
        $this->assertSame('string', $student->getKeyType());
        $this->assertFalse($student->getIncrementing());
        $this->assertFalse($student->usesTimestamps());

        $this->assertNull($document->getCreatedAtColumn());
        $this->assertSame('updated_at', $document->getUpdatedAtColumn());
        $this->assertTrue($document->usesTimestamps());

        $this->assertFalse((new DocumentType)->usesTimestamps());
        $this->assertFalse((new DocumentStatusHistory)->usesTimestamps());
        $this->assertSame('activity_log', (new ActivityLog)->getTable());
    }
}
