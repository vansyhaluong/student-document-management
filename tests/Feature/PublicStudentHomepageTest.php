<?php

namespace Tests\Feature;

use App\Enums\StudentDocumentStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\DocumentStatusHistory;
use App\Models\DocumentType;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PublicStudentHomepageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_homepage_lists_only_active_document_types(): void
    {
        $active = $this->createType('PUBLIC-ACTIVE', 'Loại hồ sơ công khai');
        $inactive = $this->createType('PUBLIC-INACTIVE', 'Loại hồ sơ đã ngừng', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee($inactive->name)
            ->assertSee(route('public.documents.lookup'), escape: false)
            ->assertSee(route('public.documents.store'), escape: false);
    }

    public function test_public_lookup_by_student_code_returns_all_documents_with_only_allowlisted_fields(): void
    {
        $staff = $this->createUser('lookup.staff', 'NỘI BỘ KHÔNG ĐƯỢC HIỂN THỊ');
        $student = $this->createStudent('SV-PUBLIC-LOOKUP', 'Trần Thị');
        $type = $this->createType('PUBLIC-LOOKUP', 'Giấy xác nhận sinh viên');
        $first = StudentDocument::query()->create([
            'document_code' => 'HS2608000001',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => StudentDocumentStatus::COMPLETED,
            'assigned_secretary_user_id' => $staff->id,
            'submitted_at' => '2026-08-10 08:00:00',
            'completed_at' => '2026-08-12 09:00:00',
            'invalid_reason' => null,
            'note' => 'GHI CHÚ NỘI BỘ TUYỆT MẬT',
        ]);
        $second = StudentDocument::query()->create([
            'document_code' => 'HS2608000002',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => StudentDocumentStatus::WAITING_FOR_RECEIPT,
            'assigned_secretary_user_id' => null,
            'submitted_at' => '2026-08-13 08:00:00',
            'completed_at' => null,
            'invalid_reason' => null,
            'note' => null,
        ]);

        $this->post(route('public.documents.lookup'), [
            'student_code' => $student->student_code,
        ])->assertRedirect(route('home').'#lookup')
            ->assertSessionHas('public_lookup.lookupPerformed', true)
            ->assertSessionHas('public_lookup.studentExists', true)
            ->assertSessionHas('public_lookup.lookupResults', function (array $results) use ($first): bool {
                return count($results) === 2
                    && array_keys($results[0]) === [
                        'document_code',
                        'document_type',
                        'status',
                        'submitted_at',
                        'completed_at',
                        'notes',
                    ]
                    && $results[0]['notes'] === null
                    && $results[1]['notes'] === $first->note;
            });

        $this->get(route('home'))
            ->assertOk()
            ->assertViewHas('studentExists', true)
            ->assertSee($first->document_code)
            ->assertSee($second->document_code)
            ->assertSee($type->name)
            ->assertSee('Hoàn tất')
            ->assertSee('10/08/2026')
            ->assertSee($student->full_name)
            ->assertSee('Ghi chú cho sinh viên')
            ->assertSee($first->note)
            ->assertDontSee($staff->full_name)
            ->assertDontSee('Ngày hoàn thành')
            ->assertDontSee('12/08/2026');
    }

    public function test_public_lookup_shows_processor_notes_when_present(): void
    {
        $student = $this->createStudent('SV-PUBLIC-NOTES');
        $type = $this->createType('PUBLIC-NOTES', 'Đơn xin bổ sung');
        $supplement = StudentDocument::query()->create([
            'document_code' => 'HS2608NOTE001',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => StudentDocumentStatus::NEEDS_SUPPLEMENT,
            'submitted_at' => '2026-08-10 08:00:00',
            'completed_at' => null,
            'invalid_reason' => null,
            'note' => 'Vui lòng nộp thêm bản sao CCCD.',
        ]);
        $cancelled = StudentDocument::query()->create([
            'document_code' => 'HS2608NOTE002',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => StudentDocumentStatus::CANCELLED,
            'submitted_at' => '2026-08-11 08:00:00',
            'completed_at' => null,
            'invalid_reason' => null,
            'note' => 'Sinh viên đã rút hồ sơ.',
        ]);
        $emptySupplement = StudentDocument::query()->create([
            'document_code' => 'HS2608NOTE003',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => StudentDocumentStatus::NEEDS_SUPPLEMENT,
            'submitted_at' => '2026-08-12 08:00:00',
            'completed_at' => null,
            'invalid_reason' => null,
            'note' => '   ',
        ]);
        $processing = StudentDocument::query()->create([
            'document_code' => 'HS2608NOTE004',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => StudentDocumentStatus::PROCESSING,
            'submitted_at' => '2026-08-13 08:00:00',
            'completed_at' => null,
            'invalid_reason' => null,
            'note' => 'Hồ sơ đang được Khoa xử lý.',
        ]);
        $fromStatusChange = StudentDocument::query()->create([
            'document_code' => 'HS2608NOTE005',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => StudentDocumentStatus::RECEIVED,
            'submitted_at' => '2026-08-14 08:00:00',
            'completed_at' => null,
            'invalid_reason' => null,
            'note' => null,
        ]);
        $staff = $this->createUser('public.notes.staff');
        DocumentStatusHistory::query()->create([
            'student_document_id' => $fromStatusChange->id,
            'status' => StudentDocumentStatus::RECEIVED->value,
            'invalid_reason' => null,
            'note' => 'Đã nhận hồ sơ, vui lòng theo dõi tiến độ.',
            'changed_by_user_id' => $staff->id,
            'changed_at' => now(),
        ]);

        $this->post(route('public.documents.lookup'), [
            'student_code' => $student->student_code,
        ])->assertRedirect(route('home').'#lookup')
            ->assertSessionHas('public_lookup.lookupResults', function (array $results) use ($supplement, $cancelled, $emptySupplement, $processing, $fromStatusChange): bool {
                $byCode = collect($results)->keyBy('document_code');

                return $byCode[$supplement->document_code]['notes'] === 'Vui lòng nộp thêm bản sao CCCD.'
                    && $byCode[$cancelled->document_code]['notes'] === 'Sinh viên đã rút hồ sơ.'
                    && $byCode[$emptySupplement->document_code]['notes'] === null
                    && $byCode[$processing->document_code]['notes'] === 'Hồ sơ đang được Khoa xử lý.'
                    && $byCode[$fromStatusChange->document_code]['notes'] === 'Đã nhận hồ sơ, vui lòng theo dõi tiến độ.';
            });

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Ghi chú cho sinh viên')
            ->assertSee('Vui lòng nộp thêm bản sao CCCD.')
            ->assertSee('Sinh viên đã rút hồ sơ.')
            ->assertSee('Hồ sơ đang được Khoa xử lý.')
            ->assertSee('Đã nhận hồ sơ, vui lòng theo dõi tiến độ.')
            ->assertDontSee('Ngày hoàn thành');
    }

    public function test_public_lookup_handles_unknown_student_no_documents_empty_and_invalid_codes(): void
    {
        $this->post(route('public.documents.lookup'), [
            'student_code' => 'SV-KHONG-TON-TAI',
        ])->assertRedirect(route('home').'#lookup');
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Không tìm thấy sinh viên với mã số đã nhập');

        $student = $this->createStudent('SV-PUBLIC-EMPTY');
        $this->post(route('public.documents.lookup'), [
            'student_code' => $student->student_code,
        ])->assertRedirect(route('home').'#lookup');
        $this->get(route('home'))
            ->assertOk()
            ->assertSee($student->full_name)
            ->assertSee('chưa có hồ sơ nào trong hệ thống');

        $this->from(route('home'))->post(route('public.documents.lookup'), [
            'student_code' => '   ',
        ])->assertRedirect(route('home').'#lookup')
            ->assertSessionHasErrors(['student_code'], null, 'lookup');

        $this->from(route('home'))->post(route('public.documents.lookup'), [
            'student_code' => '../invalid',
        ])->assertRedirect(route('home').'#lookup')
            ->assertSessionHasErrors(['student_code'], null, 'lookup');

        $this->get(route('public.documents.lookup'))->assertStatus(405);
    }

    public function test_public_submission_rejects_unknown_student_and_inactive_type(): void
    {
        $inactive = $this->createType('PUBLIC-DISABLED', 'Loại hồ sơ không khả dụng', false);

        $this->from(route('home'))->post(route('public.documents.store'), [
            'student_code' => 'SV-KHONG-TON-TAI',
            'document_type_id' => $inactive->id,
        ])->assertRedirect(route('home').'#submission')
            ->assertSessionHasErrors(
                ['student_code', 'document_type_id'],
                null,
                'submission',
            );

        $this->assertDatabaseMissing('student_documents', [
            'student_code' => 'SV-KHONG-TON-TAI',
        ]);
    }

    public function test_public_submission_creates_document_without_history_and_with_null_causer_audit(): void
    {
        $student = $this->createStudent('SV-PUBLIC-CREATE');
        $type = $this->createType('PUBLIC-CREATE', 'Đơn đề nghị công khai');
        $injectedUser = $this->createUser('injection.user');

        $response = $this->post(route('public.documents.store'), [
            'student_code' => "  {$student->student_code}  ",
            'document_type_id' => $type->id,
            'document_code' => 'INJECTED-CODE',
            'status' => StudentDocumentStatus::COMPLETED->value,
            'assigned_secretary_user_id' => $injectedUser->id,
            'note' => 'INJECTED-NOTE',
        ])->assertRedirect(route('home').'#submission')
            ->assertSessionHas('public_document_code');

        $documentCode = $response->getSession()->get('public_document_code');
        $this->assertIsString($documentCode);
        $this->assertMatchesRegularExpression('/^HS\d{10}$/', $documentCode);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Nộp hồ sơ thành công')
            ->assertSee('Bạn có thể dùng mã số sinh viên để theo dõi trạng thái xử lý.')
            ->assertDontSee('Sao chép mã')
            ->assertDontSee('Đến phần tra cứu')
            ->assertDontSee($documentCode)
            ->assertDontSee('value="'.$student->student_code.'"', false);

        $document = StudentDocument::query()->where('document_code', $documentCode)->firstOrFail();
        $this->assertSame($student->student_code, $document->student_code);
        $this->assertSame($type->id, $document->document_type_id);
        $this->assertSame(StudentDocumentStatus::WAITING_FOR_RECEIPT, $document->status);
        $this->assertNull($document->assigned_secretary_user_id);
        $this->assertNull($document->note);
        $this->assertDatabaseMissing('document_status_history', [
            'student_document_id' => $document->id,
        ]);

        $audit = ActivityLog::query()
            ->where('event', 'student_document.created')
            ->where('subject_id', $document->id)
            ->firstOrFail();
        $this->assertNull($audit->causer_type);
        $this->assertNull($audit->causer_id);
        $this->assertSame(['document_code' => $documentCode], $audit->properties);
    }

    public function test_generated_public_document_codes_are_unique(): void
    {
        $student = $this->createStudent('SV-PUBLIC-UNIQUE');
        $type = $this->createType('PUBLIC-UNIQUE', 'Loại kiểm tra mã duy nhất');

        $firstCode = $this->post(route('public.documents.store'), [
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
        ])->getSession()->get('public_document_code');
        $secondCode = $this->post(route('public.documents.store'), [
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
        ])->getSession()->get('public_document_code');

        $this->assertNotSame($firstCode, $secondCode);
        $this->assertSame(2, StudentDocument::query()
            ->whereIn('document_code', [$firstCode, $secondCode])
            ->count());
    }

    public function test_authenticated_workflow_after_public_creation_writes_normal_history(): void
    {
        $student = $this->createStudent('SV-PUBLIC-WORKFLOW');
        $type = $this->createType('PUBLIC-WORKFLOW', 'Loại kiểm tra workflow');
        $admin = $this->createUser('public.workflow.admin');

        $response = $this->post(route('public.documents.store'), [
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
        ]);
        $document = StudentDocument::query()
            ->where('document_code', $response->getSession()->get('public_document_code'))
            ->firstOrFail();

        $this->assertSame(0, $document->statusHistory()->count());

        $this->actingAs($admin)->patch(route('documents.status', $document), [
            'status' => StudentDocumentStatus::RECEIVED->value,
            'transition_note' => 'Tiếp nhận hồ sơ nộp công khai',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('document_status_history', [
            'student_document_id' => $document->id,
            'status' => StudentDocumentStatus::RECEIVED->value,
            'changed_by_user_id' => $admin->id,
        ]);
        $this->assertSame(1, $document->statusHistory()->count());
    }

    private function createStudent(string $code, string $lastName = 'Nguyễn Văn'): Student
    {
        return Student::query()->create([
            'student_code' => $code,
            'last_name' => $lastName,
            'first_name' => 'An',
        ]);
    }

    private function createType(string $code, string $name, bool $active = true): DocumentType
    {
        return DocumentType::query()->create([
            'code' => $code,
            'name' => $name,
            'description' => null,
            'is_active' => $active,
        ]);
    }

    private function createUser(
        string $username,
        string $fullName = 'Người dùng kiểm thử',
    ): User {
        return User::query()->create([
            'username' => $username,
            'password_hash' => Hash::make('test-password'),
            'full_name' => $fullName,
            'email' => null,
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);
    }
}
