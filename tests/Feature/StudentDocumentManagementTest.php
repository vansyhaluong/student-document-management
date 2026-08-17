<?php

namespace Tests\Feature;

use App\Enums\StudentDocumentStatus;
use App\Enums\UserRole;
use App\Models\DocumentStatusHistory;
use App\Models\DocumentType;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use App\Repositories\Contracts\ActivityLogRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class StudentDocumentManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_create_document_with_initial_history_and_audit(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.documents.create');
        $responsible = $this->createUser(UserRole::EMPLOYEE, 'employee.documents.assigned');
        $student = $this->createStudent('SV-P4-CREATE');
        $type = $this->createType('P4-CREATE');
        $this->actingAs($admin)->post(route('documents.store'), [
            'document_code' => 'HS-P4-CREATE',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'assigned_secretary_user_id' => $responsible->id,
            'note' => 'Hồ sơ mới',
        ])->assertRedirect();

        $document = StudentDocument::query()->where('document_code', 'HS-P4-CREATE')->firstOrFail();
        $this->assertSame(StudentDocumentStatus::WAITING_FOR_RECEIPT, $document->status);
        $this->assertNull($document->completed_at);
        $this->assertNull($document->invalid_reason);
        $this->assertDatabaseHas('document_status_history', [
            'student_document_id' => $document->id,
            'status' => StudentDocumentStatus::WAITING_FOR_RECEIPT->value,
            'changed_by_user_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'event' => 'student_document.created',
            'subject_id' => $document->id,
            'causer_type' => User::class,
            'causer_id' => $admin->id,
        ]);
    }

    public function test_created_documents_store_the_canonical_student_code(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.documents.canonical');
        $student = $this->createStudent('SV-P4-Canon');
        $type = $this->createType('P4-CANON');

        $this->actingAs($admin)->post(route('documents.store'), [
            'document_code' => 'HS-P4-CANON',
            'student_code' => strtolower($student->student_code),
            'document_type_id' => $type->id,
        ])->assertRedirect();

        $this->assertSame(
            $student->student_code,
            StudentDocument::query()->where('document_code', 'HS-P4-CANON')->value('student_code'),
        );
    }

    public function test_document_detail_and_status_history_use_safe_relationship_fallbacks(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.documents.detail');
        $document = $this->createDocument('HS-P4-DETAIL', $admin);
        $this->actingAs($admin)
            ->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee($document->student->full_name)
            ->assertSee($admin->full_name)
            ->assertSee($document->documentType->name)
            ->assertDontSee('Lưu phân công');

        $document->setRelation('student', null);
        $document->setRelation('documentType', null);
        $document->setRelation('responsibleUser', null);
        $history = (new DocumentStatusHistory)->forceFill([
            'status' => StudentDocumentStatus::RECEIVED,
            'changed_at' => now(),
        ]);
        $history->setRelation('changedBy', null);
        $document->setRelation('statusHistory', collect([$history]));

        $html = view('student-documents.show', [
            'document' => $document,
            'responsibleUsers' => collect(),
            'availableTransitions' => collect(),
        ])->render();

        $this->assertStringContainsString($document->student_code, $html);
        $this->assertStringContainsString('Không xác định', $html);
        $this->assertStringContainsString('Chưa phân công', $html);
    }

    public function test_employee_cannot_create_and_only_sees_assigned_documents(): void
    {
        $employee = $this->createUser(UserRole::EMPLOYEE, 'employee.documents.scope');
        $other = $this->createUser(UserRole::EMPLOYEE, 'employee.documents.other');
        $assigned = $this->createDocument('HS-P4-VISIBLE', $employee);
        $hidden = $this->createDocument('HS-P4-HIDDEN', $other);

        $this->actingAs($employee)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertSee($assigned->document_code)
            ->assertDontSee($hidden->document_code);
        $this->actingAs($employee)->get(route('documents.create'))->assertForbidden();
        $this->actingAs($employee)->get(route('documents.show', $hidden))->assertNotFound();
    }

    public function test_document_list_allows_inline_status_change_and_hides_responsible_column(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.documents.inline.status');
        $employee = $this->createUser(UserRole::EMPLOYEE, 'employee.documents.inline.status');
        $document = $this->createDocument('HS-P4-INLINE', $employee);

        $this->actingAs($employee)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertDontSee('data-status-autosubmit', false)
            ->assertSee('Tiếp nhận');

        $this->actingAs($admin)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertDontSee('>Người phụ trách</th>', false)
            ->assertSee('data-status-autosubmit', false)
            ->assertSee(StudentDocumentStatus::RECEIVED->label())
            ->assertSee(StudentDocumentStatus::CANCELLED->label());

        $this->actingAs($admin)
            ->from(route('documents.index'))
            ->patch(route('documents.status', $document), [
                'status' => StudentDocumentStatus::RECEIVED->value,
            ])
            ->assertRedirect(route('documents.index'));

        $this->assertSame(StudentDocumentStatus::RECEIVED, $document->fresh()->status);
    }

    public function test_assigned_employee_can_accept_waiting_document_atomically(): void
    {
        $employee = $this->createUser(UserRole::EMPLOYEE, 'employee.documents.accept');
        $document = $this->createDocument('HS-P4-ACCEPT', $employee);
        $auditRepository = $this->mock(ActivityLogRepository::class);
        $auditRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(
                static fn (array $attributes): bool => $attributes['event'] === 'student_document.status_changed',
            ));
        $auditRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(
                static fn (array $attributes): bool => $attributes['event'] === 'student_document.accepted',
            ));

        $this->actingAs($employee)->post(route('documents.accept', $document), [
            'transition_note' => 'Đã kiểm tra hồ sơ',
        ])->assertSessionHas('success');

        $this->assertSame(StudentDocumentStatus::RECEIVED, $document->fresh()->status);
        $this->assertDatabaseHas('document_status_history', [
            'student_document_id' => $document->id,
            'status' => StudentDocumentStatus::RECEIVED->value,
            'changed_by_user_id' => $employee->id,
        ]);
    }

    public function test_admin_can_assign_any_active_approved_role_and_unassigned_employee_is_denied(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.documents.assign');
        $secretary = $this->createUser(UserRole::SECRETARY, 'secretary.documents.assign');
        $employee = $this->createUser(UserRole::EMPLOYEE, 'employee.documents.denied');
        $document = $this->createDocument('HS-P4-ASSIGN', $admin);
        $this->expectAudit('student_document.assigned');

        $this->actingAs($admin)->patch(route('documents.assignment', $document), [
            'assigned_secretary_user_id' => $secretary->id,
        ])->assertSessionHas('success');

        $this->assertSame($secretary->id, $document->fresh()->assigned_secretary_user_id);
        $this->actingAs($employee)
            ->post(route('documents.accept', $document))
            ->assertForbidden();
    }

    public function test_invalid_transition_does_not_write_history_or_change_document(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.documents.invalid.transition');
        $document = $this->createDocument('HS-P4-BAD-TRANSITION', $admin);
        $historyCount = $document->statusHistory()->count();

        $this->actingAs($admin)->patch(route('documents.status', $document), [
            'status' => StudentDocumentStatus::COMPLETED->value,
        ])->assertSessionHas('error');

        $this->assertSame(StudentDocumentStatus::WAITING_FOR_RECEIPT, $document->fresh()->status);
        $this->assertSame($historyCount, $document->statusHistory()->count());
    }

    public function test_status_changes_enforce_completed_and_invalid_invariants(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.documents.invariants');
        $completed = $this->createDocument('HS-P4-COMPLETE', $admin, StudentDocumentStatus::PROCESSING);
        $invalid = $this->createDocument('HS-P4-INVALID', $admin, StudentDocumentStatus::RECEIVED);
        $this->mock(ActivityLogRepository::class)
            ->shouldReceive('create')
            ->twice()
            ->with(Mockery::on(
                static fn (array $attributes): bool => $attributes['event'] === 'student_document.status_changed',
            ));

        $this->actingAs($admin)->patch(route('documents.status', $completed), [
            'status' => StudentDocumentStatus::COMPLETED->value,
        ])->assertSessionHas('success');

        $completed->refresh();
        $this->assertNotNull($completed->completed_at);
        $this->assertNull($completed->invalid_reason);

        $this->actingAs($admin)->patch(route('documents.status', $invalid), [
            'status' => StudentDocumentStatus::INVALID->value,
            'invalid_reason' => 'Thiếu giấy tờ bắt buộc',
        ])->assertSessionHas('success');

        $invalid->refresh();
        $this->assertNull($invalid->completed_at);
        $this->assertSame('Thiếu giấy tờ bắt buộc', $invalid->invalid_reason);
    }

    public function test_document_creation_rolls_back_when_audit_fails(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.documents.rollback');
        $student = $this->createStudent('SV-P4-ROLLBACK');
        $type = $this->createType('P4-ROLLBACK');
        $this->mock(ActivityLogRepository::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new RuntimeException('Audit unavailable'));
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->post(route('documents.store'), [
                'document_code' => 'HS-P4-ROLLBACK',
                'student_code' => $student->student_code,
                'document_type_id' => $type->id,
                'note' => null,
            ]);
            $this->fail('Expected audit failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        }

        $this->assertDatabaseMissing('student_documents', ['document_code' => 'HS-P4-ROLLBACK']);
    }

    private function createUser(UserRole $role, string $username): User
    {
        return User::query()->create([
            'username' => $username,
            'password_hash' => Hash::make('test-password'),
            'full_name' => 'Người dùng kiểm thử',
            'email' => null,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function expectAudit(string $event): void
    {
        $this->mock(ActivityLogRepository::class)
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(
                static fn (array $attributes): bool => $attributes['event'] === $event,
            ));
    }

    private function createStudent(string $code): Student
    {
        return Student::query()->create([
            'student_code' => $code,
            'last_name' => 'Nguyễn Văn',
            'first_name' => 'An',
        ]);
    }

    private function createType(string $code): DocumentType
    {
        return DocumentType::query()->create([
            'code' => $code,
            'name' => 'Loại hồ sơ Phase 4',
            'description' => null,
            'is_active' => true,
        ]);
    }

    private function createDocument(
        string $code,
        User $responsible,
        StudentDocumentStatus $status = StudentDocumentStatus::WAITING_FOR_RECEIPT,
    ): StudentDocument {
        $suffix = substr(md5($code), 0, 8);
        $student = $this->createStudent("SV-{$suffix}");
        $type = $this->createType("T-{$suffix}");

        return StudentDocument::query()->create([
            'document_code' => $code,
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => $status,
            'assigned_secretary_user_id' => $responsible->id,
            'submitted_at' => now(),
            'completed_at' => null,
            'invalid_reason' => null,
            'note' => null,
        ]);
    }
}
