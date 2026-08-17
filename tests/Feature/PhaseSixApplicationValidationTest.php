<?php

namespace Tests\Feature;

use App\Enums\StudentDocumentStatus;
use App\Enums\UserRole;
use App\Models\DocumentType;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use App\Repositories\Contracts\ActivityLogRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class PhaseSixApplicationValidationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_document_search_and_filters_apply_after_access_scope(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'phase6.search.admin');
        $employee = $this->createUser(UserRole::EMPLOYEE, 'phase6.search.employee');
        $otherEmployee = $this->createUser(UserRole::EMPLOYEE, 'phase6.search.other');
        $includedType = $this->createType('P6-FILTER-IN', 'Loại lọc Phase 6');
        $otherType = $this->createType('P6-FILTER-OUT', 'Loại ngoài lọc Phase 6');

        $matched = $this->createDocument(
            'P6-SEARCH-MATCH',
            $employee,
            $includedType,
            StudentDocumentStatus::PROCESSING,
            '2026-05-10 08:00:00',
            'P6-SV-MATCH',
            'P6UniqueLast',
            'P6UniqueFirst',
        );
        $this->createDocument(
            'P6-SEARCH-TYPE',
            $employee,
            $otherType,
            StudentDocumentStatus::PROCESSING,
            '2026-05-10 08:00:00',
        );
        $this->createDocument(
            'P6-SEARCH-STATUS',
            $employee,
            $includedType,
            StudentDocumentStatus::RECEIVED,
            '2026-05-10 08:00:00',
        );
        $this->createDocument(
            'P6-SEARCH-DATE',
            $employee,
            $includedType,
            StudentDocumentStatus::PROCESSING,
            '2026-05-20 08:00:00',
        );
        $hidden = $this->createDocument(
            'P6-SEARCH-HIDDEN',
            $otherEmployee,
            $includedType,
            StudentDocumentStatus::PROCESSING,
            '2026-05-10 08:00:00',
            'P6-SV-HIDDEN',
            'P6UniqueLast',
            'HiddenStudent',
        );

        $this->actingAs($admin)
            ->get(route('documents.index', [
                'keyword' => $matched->document_code,
                'document_type_id' => $includedType->id,
                'status' => StudentDocumentStatus::PROCESSING->value,
                'responsible_user_id' => $employee->id,
                'submitted_from' => '2026-05-01',
                'submitted_to' => '2026-05-15',
            ]))
            ->assertOk()
            ->assertSee($matched->document_code)
            ->assertDontSee('P6-SEARCH-TYPE')
            ->assertDontSee('P6-SEARCH-STATUS')
            ->assertDontSee('P6-SEARCH-DATE')
            ->assertDontSee($hidden->document_code);

        $this->actingAs($admin)
            ->get(route('documents.index', ['keyword' => 'P6-SV-MATCH']))
            ->assertOk()
            ->assertSee($matched->document_code)
            ->assertDontSee($hidden->document_code);

        $this->actingAs($admin)
            ->get(route('documents.index', ['keyword' => 'P6UniqueFirst']))
            ->assertOk()
            ->assertSee($matched->document_code)
            ->assertDontSee($hidden->document_code);

        $this->actingAs($employee)
            ->get(route('documents.index', ['keyword' => 'P6UniqueLast']))
            ->assertOk()
            ->assertSee($matched->document_code)
            ->assertDontSee($hidden->document_code);
    }

    public function test_document_pagination_is_limited_to_the_filtered_result_set(): void
    {
        $secretary = $this->createUser(UserRole::SECRETARY, 'phase6.page.secretary');
        $type = $this->createType('P6-PAGE', 'Loại phân trang Phase 6');
        $first = $this->createDocument(
            'P6-PAGE-FIRST',
            $secretary,
            $type,
            StudentDocumentStatus::WAITING_FOR_RECEIPT,
            '2026-06-02 08:00:00',
        );
        $second = $this->createDocument(
            'P6-PAGE-SECOND',
            $secretary,
            $type,
            StudentDocumentStatus::WAITING_FOR_RECEIPT,
            '2026-06-01 08:00:00',
        );

        $this->actingAs($secretary)
            ->get(route('documents.index', [
                'document_type_id' => $type->id,
                'sort' => 'submitted_at',
                'direction' => 'desc',
                'per_page' => 1,
            ]))
            ->assertOk()
            ->assertSee($first->document_code)
            ->assertDontSee($second->document_code);

        $this->actingAs($secretary)
            ->get(route('documents.index', [
                'document_type_id' => $type->id,
                'sort' => 'submitted_at',
                'direction' => 'desc',
                'per_page' => 1,
                'page' => 2,
            ]))
            ->assertOk()
            ->assertSee($second->document_code)
            ->assertDontSee($first->document_code);
    }

    public function test_document_list_rejects_invalid_filters(): void
    {
        $secretary = $this->createUser(UserRole::SECRETARY, 'phase6.invalid.filters');

        $this->actingAs($secretary)
            ->from(route('documents.index'))
            ->get(route('documents.index', [
                'status' => 'unapproved-status',
                'submitted_from' => '2026-07-10',
                'submitted_to' => '2026-07-01',
                'sort' => 'unexpected_column',
                'per_page' => 1000,
            ]))
            ->assertRedirect(route('documents.index'))
            ->assertSessionHasErrors(['status', 'submitted_to', 'sort', 'per_page']);
    }

    public function test_document_code_must_be_unique_and_is_not_changed_on_update(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'phase6.code.admin');
        $existing = $this->createDocument(
            'P6-CODE-EXISTING',
            $admin,
            $this->createType('P6-CODE-EXISTING', 'Loại mã trùng'),
        );
        $student = $this->createStudent('P6-SV-UNIQUE-CODE');
        $type = $this->createType('P6-CODE-NEW', 'Loại mã mới');

        $this->actingAs($admin)
            ->from(route('documents.create'))
            ->post(route('documents.store'), [
                'document_code' => $existing->document_code,
                'student_code' => $student->student_code,
                'document_type_id' => $type->id,
            ])
            ->assertRedirect(route('documents.create'))
            ->assertSessionHasErrors('document_code');

        $this->actingAs($admin)
            ->put(route('documents.update', $existing), [
                'document_code' => 'P6-CODE-CHANGED',
                'student_code' => $existing->student_code,
                'document_type_id' => $existing->document_type_id,
                'note' => 'Cập nhật không đổi mã',
            ])
            ->assertRedirect(route('documents.show', $existing));

        $this->assertSame('P6-CODE-EXISTING', $existing->fresh()->document_code);
        $this->assertSame('Cập nhật không đổi mã', $existing->fresh()->note);
    }

    public function test_status_change_rolls_back_when_audit_write_fails(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'phase6.status.rollback');
        $document = $this->createDocument(
            'P6-STATUS-ROLLBACK',
            $admin,
            $this->createType('P6-STATUS-ROLLBACK', 'Loại rollback trạng thái'),
        );
        $historyCount = $document->statusHistory()->count();
        $this->mock(ActivityLogRepository::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new RuntimeException('Audit unavailable'));
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->patch(route('documents.status', $document), [
                'status' => StudentDocumentStatus::RECEIVED->value,
            ]);
            $this->fail('Expected audit failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        }

        $document->refresh();
        $this->assertSame(StudentDocumentStatus::WAITING_FOR_RECEIPT, $document->status);
        $this->assertSame($historyCount, $document->statusHistory()->count());
    }

    public function test_assignment_rolls_back_when_audit_write_fails(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'phase6.assign.rollback');
        $secretary = $this->createUser(UserRole::SECRETARY, 'phase6.assign.target');
        $document = $this->createDocument(
            'P6-ASSIGN-ROLLBACK',
            $admin,
            $this->createType('P6-ASSIGN-ROLLBACK', 'Loại rollback phân công'),
        );
        $this->mock(ActivityLogRepository::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new RuntimeException('Audit unavailable'));
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->patch(route('documents.assignment', $document), [
                'assigned_secretary_user_id' => $secretary->id,
            ]);
            $this->fail('Expected audit failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        }

        $this->assertSame($admin->id, $document->fresh()->assigned_secretary_user_id);
    }

    public function test_guest_cannot_access_protected_application_modules(): void
    {
        $this->get(route('documents.index'))->assertRedirect(route('login'));
        $this->get(route('users.index'))->assertRedirect(route('login'));
        $this->get(route('document-types.index'))->assertRedirect(route('login'));
        $this->get(route('reports.index'))->assertRedirect(route('login'));
        $this->get(route('activity-log.index'))->assertRedirect(route('login'));
    }

    public function test_status_history_has_no_application_update_or_delete_routes(): void
    {
        $this->assertNull(app('router')->getRoutes()->getByName('documents.history.update'));
        $this->assertNull(app('router')->getRoutes()->getByName('documents.history.destroy'));
        $this->assertNull(app('router')->getRoutes()->getByAction(
            'App\\Http\\Controllers\\StudentDocumentController@destroy',
        ));
    }

    private function createUser(UserRole $role, string $username): User
    {
        return User::query()->create([
            'username' => $username,
            'password_hash' => Hash::make('test-password'),
            'full_name' => $username,
            'email' => null,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function createStudent(
        string $code,
        string $lastName = 'Nguyễn',
        string $firstName = 'An',
    ): Student {
        return Student::query()->create([
            'student_code' => $code,
            'last_name' => $lastName,
            'first_name' => $firstName,
        ]);
    }

    private function createType(string $code, string $name): DocumentType
    {
        return DocumentType::query()->create([
            'code' => $code,
            'name' => $name,
            'description' => null,
            'is_active' => true,
        ]);
    }

    private function createDocument(
        string $code,
        User $responsible,
        DocumentType $type,
        StudentDocumentStatus $status = StudentDocumentStatus::WAITING_FOR_RECEIPT,
        string $submittedAt = '2026-05-10 08:00:00',
        ?string $studentCode = null,
        string $lastName = 'Nguyễn',
        string $firstName = 'An',
    ): StudentDocument {
        $student = $this->createStudent(
            $studentCode ?? 'P6-'.substr(md5($code), 0, 10),
            $lastName,
            $firstName,
        );

        return StudentDocument::query()->create([
            'document_code' => $code,
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => $status,
            'assigned_secretary_user_id' => $responsible->id,
            'submitted_at' => Carbon::parse($submittedAt),
            'completed_at' => null,
            'invalid_reason' => null,
            'note' => null,
        ]);
    }
}
