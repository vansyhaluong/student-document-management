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
        $student = $this->createStudent('SV-P4-CREATE');
        $type = $this->createType('P4-CREATE');
        $this->actingAs($admin)->post(route('documents.store'), [
            'document_code' => 'HS-P4-CREATE',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
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
            ->assertDontSee('Người phụ trách')
            ->assertDontSee('Lưu phân công')
            ->assertDontSee('Tiếp nhận hồ sơ');

        $document->setRelation('student', null);
        $document->setRelation('documentType', null);
        $history = (new DocumentStatusHistory)->forceFill([
            'status' => StudentDocumentStatus::RECEIVED,
            'changed_at' => now(),
        ]);
        $history->setRelation('changedBy', null);
        $document->setRelation('statusHistory', collect([$history]));

        $html = view('student-documents.show', [
            'document' => $document,
            'availableTransitions' => collect(),
        ])->render();

        $this->assertStringContainsString($document->student_code, $html);
        $this->assertStringContainsString('Lý do', $html);
        $this->assertStringContainsString('Không xác định', $html);
        $this->assertStringNotContainsString('Ghi chú hồ sơ', $html);
        $this->assertStringNotContainsString('Chưa phân công', $html);
        $this->assertStringNotContainsString('Người phụ trách', $html);
    }

    public function test_employee_cannot_create_but_can_see_all_documents(): void
    {
        $employee = $this->createUser(UserRole::EMPLOYEE, 'employee.documents.scope');
        $other = $this->createUser(UserRole::EMPLOYEE, 'employee.documents.other');
        $own = $this->createDocument('HS-P4-VISIBLE', $employee);
        $otherDocument = $this->createDocument('HS-P4-HIDDEN', $other);

        $this->actingAs($employee)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertSee($own->document_code)
            ->assertSee($otherDocument->document_code);
        $this->actingAs($employee)->get(route('documents.create'))->assertForbidden();
        $this->actingAs($employee)->get(route('documents.show', $otherDocument))->assertOk();
    }

    public function test_document_list_allows_inline_status_change_and_hides_responsible_column(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.documents.inline.status');
        $employee = $this->createUser(UserRole::EMPLOYEE, 'employee.documents.inline.status');
        $document = $this->createDocument('HS-P4-INLINE', $employee);

        $this->actingAs($employee)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertSee('data-status-autosubmit', false)
            ->assertDontSee('Xác nhận tiếp nhận')
            ->assertDontSee('Người phụ trách');

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

    public function test_employee_can_change_waiting_document_to_received(): void
    {
        $employee = $this->createUser(UserRole::EMPLOYEE, 'employee.documents.status');
        $document = $this->createDocument('HS-P4-RECEIVE', $employee);
        $this->expectAudit('student_document.status_changed');

        $this->actingAs($employee)->patch(route('documents.status', $document), [
            'status' => StudentDocumentStatus::RECEIVED->value,
            'transition_note' => 'Đã kiểm tra hồ sơ',
        ])->assertSessionHas('success');

        $document->refresh();
        $this->assertSame(StudentDocumentStatus::RECEIVED, $document->status);
        $this->assertSame('Đã kiểm tra hồ sơ', $document->note);

        $this->actingAs($employee)
            ->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('Lý do')
            ->assertSee('Đã kiểm tra hồ sơ')
            ->assertDontSee('Ghi chú chuyển trạng thái')
            ->assertDontSee('Ghi chú hồ sơ');
        $this->assertDatabaseHas('document_status_history', [
            'student_document_id' => $document->id,
            'status' => StudentDocumentStatus::RECEIVED->value,
            'changed_by_user_id' => $employee->id,
        ]);
    }

    public function test_assignment_and_acceptance_routes_are_removed(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.documents.assign.removed');
        $document = $this->createDocument('HS-P4-NO-ASSIGN', $admin);

        $this->actingAs($admin)
            ->patch('/documents/'.$document->id.'/assignment', [
                'assigned_secretary_user_id' => $admin->id,
            ])
            ->assertNotFound();
        $this->actingAs($admin)
            ->post('/documents/'.$document->id.'/accept', [
                'transition_note' => 'Không còn thao tác tiếp nhận.',
            ])
            ->assertNotFound();
        $this->assertNull(app('router')->getRoutes()->getByName('documents.assignment'));
        $this->assertNull(app('router')->getRoutes()->getByName('documents.accept'));
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
            'submitted_at' => now(),
            'completed_at' => null,
            'invalid_reason' => null,
            'note' => null,
        ]);
    }
}
