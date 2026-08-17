<?php

namespace Tests\Feature;

use App\Enums\StudentDocumentStatus;
use App\Enums\UserRole;
use App\Models\DocumentType;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublicStudentDocumentApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_valid_student_with_documents_returns_public_safe_payload(): void
    {
        $staff = $this->createUser('api.lookup.staff', 'NỘI BỘ KHÔNG ĐƯỢC HIỂN THỊ');
        $student = $this->createStudent('SV-API-LOOKUP', 'SINH VIÊN KHÔNG ĐƯỢC HIỂN THỊ');
        $otherStudent = $this->createStudent('SV-API-OTHER');
        $type = $this->createType('API-LOOKUP', 'Giấy xác nhận sinh viên');
        $completed = StudentDocument::query()->create([
            'document_code' => 'HS2608API0001',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => StudentDocumentStatus::COMPLETED,
            'assigned_secretary_user_id' => $staff->id,
            'submitted_at' => '2026-08-10 08:00:00',
            'completed_at' => '2026-08-12 09:00:00',
            'invalid_reason' => null,
            'note' => 'GHI CHÚ NỘI BỘ TUYỆT MẬT',
        ]);
        $waiting = StudentDocument::query()->create([
            'document_code' => 'HS2608API0002',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => StudentDocumentStatus::WAITING_FOR_RECEIPT,
            'assigned_secretary_user_id' => null,
            'submitted_at' => '2026-08-13 08:00:00',
            'completed_at' => null,
            'invalid_reason' => null,
            'note' => null,
        ]);
        StudentDocument::query()->create([
            'document_code' => 'HS2608API0003',
            'student_code' => $otherStudent->student_code,
            'document_type_id' => $type->id,
            'status' => StudentDocumentStatus::PROCESSING,
            'assigned_secretary_user_id' => $staff->id,
            'submitted_at' => '2026-08-14 08:00:00',
            'completed_at' => null,
            'invalid_reason' => null,
            'note' => 'HỒ SƠ SINH VIÊN KHÁC',
        ]);

        $response = $this->getJson($this->documentsUrl($student->student_code))
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Lấy dữ liệu thành công',
                'data' => [
                    'student_code' => $student->student_code,
                    'student_exists' => true,
                    'documents' => [
                        [
                            'document_code' => $waiting->document_code,
                            'document_type' => $type->name,
                            'status' => StudentDocumentStatus::WAITING_FOR_RECEIPT->value,
                            'status_label' => StudentDocumentStatus::WAITING_FOR_RECEIPT->label(),
                            'submitted_at' => '2026-08-13',
                            'completed_at' => null,
                        ],
                        [
                            'document_code' => $completed->document_code,
                            'document_type' => $type->name,
                            'status' => StudentDocumentStatus::COMPLETED->value,
                            'status_label' => StudentDocumentStatus::COMPLETED->label(),
                            'submitted_at' => '2026-08-10',
                            'completed_at' => '2026-08-12',
                        ],
                    ],
                ],
            ]);

        $this->assertSame(
            ['document_code', 'document_type', 'status', 'status_label', 'submitted_at', 'completed_at'],
            array_keys($response->json('data.documents.0')),
        );
        $this->assertStringNotContainsString('assigned_secretary_user_id', (string) $response->getContent());
        $this->assertStringNotContainsString('invalid_reason', (string) $response->getContent());
        $this->assertStringNotContainsString('password_hash', (string) $response->getContent());
        $this->assertStringNotContainsString('changed_by_user_id', (string) $response->getContent());
        $this->assertStringNotContainsString('activity_log', (string) $response->getContent());
        $this->assertStringNotContainsString($staff->full_name, (string) $response->getContent());
        $this->assertStringNotContainsString($student->last_name, (string) $response->getContent());
        $this->assertStringNotContainsString('GHI CHÚ NỘI BỘ TUYỆT MẬT', (string) $response->getContent());
        $this->assertStringNotContainsString('HS2608API0003', (string) $response->getContent());
    }

    public function test_valid_student_without_documents_returns_empty_list(): void
    {
        $student = $this->createStudent('SV-API-EMPTY');

        $this->getJson($this->documentsUrl($student->student_code))
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Lấy dữ liệu thành công',
                'data' => [
                    'student_code' => $student->student_code,
                    'student_exists' => true,
                    'documents' => [],
                ],
            ]);
    }

    public function test_unknown_student_returns_success_with_empty_documents(): void
    {
        $this->getJson($this->documentsUrl('SV-API-MISSING'))
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Lấy dữ liệu thành công',
                'data' => [
                    'student_code' => 'SV-API-MISSING',
                    'student_exists' => false,
                    'documents' => [],
                ],
            ]);
    }

    public function test_invalid_student_codes_return_standardized_validation_errors(): void
    {
        $this->getJson($this->documentsUrl('invalid_code'))
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => [
                    'student_code' => ['Mã số sinh viên không đúng định dạng.'],
                ],
            ]);

        $this->getJson($this->documentsUrl('123456789012345678901'))
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => [
                    'student_code' => ['Mã số sinh viên không được vượt quá 20 ký tự.'],
                ],
            ]);
    }

    public function test_response_does_not_leak_internal_document_fields(): void
    {
        $staff = $this->createUser('api.leak.staff', 'Nhân viên ẩn');
        $student = $this->createStudent('SV-API-LEAK', 'Họ bí mật');
        $type = $this->createType('API-LEAK', 'Đơn xin xác nhận');
        StudentDocument::query()->create([
            'document_code' => 'HS2608APILEAK',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => StudentDocumentStatus::INVALID,
            'assigned_secretary_user_id' => $staff->id,
            'submitted_at' => '2026-08-11 08:00:00',
            'completed_at' => null,
            'invalid_reason' => 'LY DO NOI BO KHONG DUOC LO',
            'note' => 'GHI CHU NOI BO KHONG DUOC LO',
        ]);

        $response = $this->getJson($this->documentsUrl($student->student_code))
            ->assertOk()
            ->assertJsonPath('data.student_exists', true)
            ->assertJsonPath('data.documents.0.status', StudentDocumentStatus::INVALID->value)
            ->assertJsonPath('data.documents.0.status_label', StudentDocumentStatus::INVALID->label());

        $document = $response->json('data.documents.0');
        $this->assertIsArray($document);
        $this->assertSame(
            ['document_code', 'document_type', 'status', 'status_label', 'submitted_at', 'completed_at'],
            array_keys($document),
        );
        $this->assertArrayNotHasKey('id', $document);
        $this->assertArrayNotHasKey('note', $document);
        $this->assertArrayNotHasKey('invalid_reason', $document);
        $this->assertArrayNotHasKey('assigned_secretary_user_id', $document);
        $this->assertArrayNotHasKey('password_hash', $document);
        $this->assertStringNotContainsString('LY DO NOI BO KHONG DUOC LO', (string) $response->getContent());
        $this->assertStringNotContainsString('GHI CHU NOI BO KHONG DUOC LO', (string) $response->getContent());
        $this->assertStringNotContainsString($staff->full_name, (string) $response->getContent());
    }

    public function test_public_api_route_is_rate_limited(): void
    {
        $route = Route::getRoutes()->getByName('api.students.documents');

        $this->assertInstanceOf(LaravelRoute::class, $route);
        $this->assertContains('throttle:30,1', $route->gatherMiddleware());
    }

    public function test_web_public_lookup_still_uses_display_date_and_status_label(): void
    {
        $student = $this->createStudent('SV-API-WEB-REG');
        $type = $this->createType('API-WEB-REG', 'Giấy xác nhận sinh viên');
        StudentDocument::query()->create([
            'document_code' => 'HS2608APIWEB1',
            'student_code' => $student->student_code,
            'document_type_id' => $type->id,
            'status' => StudentDocumentStatus::COMPLETED,
            'assigned_secretary_user_id' => null,
            'submitted_at' => '2026-08-10 08:00:00',
            'completed_at' => '2026-08-12 09:00:00',
            'invalid_reason' => null,
            'note' => null,
        ]);

        $this->post(route('public.documents.lookup'), [
            'student_code' => $student->student_code,
        ])->assertOk()
            ->assertSee('HS2608APIWEB1')
            ->assertSee('Hoàn tất')
            ->assertSee('10/08/2026')
            ->assertSee('12/08/2026')
            ->assertDontSee('waiting_for_receipt')
            ->assertDontSee('2026-08-10');
    }

    private function documentsUrl(string $studentCode): string
    {
        return '/api/students/'.$studentCode.'/documents';
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
